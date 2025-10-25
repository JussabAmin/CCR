// Dados para o Gráfico de Evolução Mensal (Entradas vs. Saídas)
const dadosAtivos = JSON.parse('<?php echo json_encode($json_ativos); ?>');
const dadosDespesas = JSON.parse('<?php echo json_encode($json_despesas); ?>');
const labels = [...new Set([...dadosAtivos.map(d => d.mes), ...dadosDespesas.map(d => d.mes)])].sort();

const totaisAtivos = labels.map(mes => dadosAtivos.find(d => d.mes === mes)?.total || 0);
const totaisDespesas = labels.map(mes => dadosDespesas.find(d => d.mes === mes)?.total || 0);

const dataBalance = {
    labels: labels,
    datasets: [{
        label: 'Entradas',
        backgroundColor: 'rgba(40, 167, 69, 0.5)',
        borderColor: 'rgba(40, 167, 69, 1)',
        data: totaisAtivos,
    }, {
        label: 'Despesas',
        backgroundColor: 'rgba(220, 53, 69, 0.5)',
        borderColor: 'rgba(220, 53, 69, 1)',
        data: totaisDespesas,
    }]
};

const configBalance = {
    type: 'bar',
    data: dataBalance,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
};

const balanceChart = new Chart(
    document.getElementById('balanceChart'),
    configBalance
);

// Dados para o Gráfico de Pizza (Despesas por Categoria)
const dadosDespesasCategoria = JSON.parse('<?php echo json_encode($json_despesas_categoria); ?>');
const categorias = dadosDespesasCategoria.map(d => d.categoria);
const totaisCategorias = dadosDespesasCategoria.map(d => d.total);

const dataExpenses = {
    labels: categorias,
    datasets: [{
        label: 'Total de Despesas',
        data: totaisCategorias,
        backgroundColor: [
            '#dc3545', '#ffc107', '#28a745', '#17a2b8', '#6c757d', '#fd7e14', '#e83e8c', '#6f42c1', '#20c997', '#007bff'
        ],
        hoverOffset: 4
    }]
};

const configExpenses = {
    type: 'pie',
    data: dataExpenses,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Distribuição de Despesas por Categoria'
            }
        }
    },
};

const expensesChart = new Chart(
    document.getElementById('expensesChart'),
    configExpenses
);