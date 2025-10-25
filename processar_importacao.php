<?php

/**
 * Habilita a tipagem estrita para maior segurança do código.
 */
declare(strict_types=1);

/**
 * Inclui os arquivos necessários. O uso de __DIR__ garante que os caminhos
 * estarão sempre corretos, não importa como o script seja chamado.
 */
require_once __DIR__ . '/seguranca.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SimpleXLSX.php';

/**
 * Importa a classe SimpleXLSX do seu namespace.
 */
use Shuchkin\SimpleXLSX;

/**
 * Classe ImportadorExcel
 * Organiza toda a lógica de importação de forma moderna e orientada a objetos.
 */
class ImportadorExcel
{
    /**
     * Construtor com promoção de propriedades (recurso do PHP 8.0).
     */
    public function __construct(
        private readonly mysqli $conexao,
        private readonly int $idUsuario
    ) {
    }

    /**
     * Orquestra todo o processo de importação.
     * @throws Exception se o arquivo não for válido ou houver um erro no banco de dados.
     */
    public function processar(string $caminhoArquivo): void
    {
        $xlsx = SimpleXLSX::parse($caminhoArquivo);

        if (!$xlsx) {
            throw new Exception('Erro ao ler o arquivo Excel: ' . SimpleXLSX::parseError());
        }

        $linhas = $xlsx->rows();
        
        // Remove a primeira linha, que é o cabeçalho
        array_shift($linhas);

        if (empty($linhas)) {
            return; // Se não há dados após o cabeçalho, encerra com sucesso.
        }
        
        $this->inserirDados($linhas);
    }

    /**
     * Insere as linhas de dados no banco de dados usando uma transação para segurança.
     * @param array $linhas Os dados extraídos da planilha.
     */
    private function inserirDados(array $linhas): void
    {
        $sql = "INSERT INTO despesas (valor, data, categoria, descricao, usuario_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexao->prepare($sql);

        $this->conexao->begin_transaction();

        try {
            // AQUI ESTÁ A CORREÇÃO: Copiamos a propriedade readonly para uma variável local.
            $idUsuario = $this->idUsuario;

            foreach ($linhas as $linha) {
                $valor =     $linha[0] ?? null;
                $data =      $linha[1] ?? null;
                $categoria = $linha[2] ?? '';
                $descricao = $linha[3] ?? '';

                if (empty($valor) || empty($data)) {
                    continue;
                }

                $valorFormatado = (float) str_replace(',', '.', (string) $valor);
                $dataFormatada = $this->formatarData($data);

                // E usamos a variável local aqui, em vez de $this->idUsuario
                $stmt->bind_param("dsssi", $valorFormatado, $dataFormatada, $categoria, $descricao, $idUsuario);
                $stmt->execute();
            }

            $this->conexao->commit();
        } catch (Exception $e) {
            $this->conexao->rollback();
            throw new Exception("Erro ao inserir dados no banco: " . $e->getMessage());
        } finally {
            $stmt->close();
        }
    }

    /**
     * Converte datas do Excel para o formato 'Y-m-d'.
     */
    private function formatarData(mixed $data): string
    {
        if (is_numeric($data)) {
            $unixTimestamp = ($data - 25569) * 86400;
            return date('Y-m-d', $unixTimestamp);
        }
        return date('Y-m-d', strtotime(str_replace('/', '-', (string) $data)));
    }
}


// --- LÓGICA PRINCIPAL DE EXECUÇÃO DO SCRIPT ---

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_FILES["excel_file"]["tmp_name"])) {
    header("location: importar_excel.php");
    exit;
}

$status = 'success';

try {
    $importador = new ImportadorExcel($link, $_SESSION["id"]);
    $importador->processar($_FILES["excel_file"]["tmp_name"]);

} catch (Exception $e) {
    $status = 'error';
    error_log("Falha na importação do Excel: " . $e->getMessage());
} finally {
    if (isset($link) && $link->ping()) {
        $link->close();
    }
}

header("location: importar_excel.php?status=" . $status);
exit;