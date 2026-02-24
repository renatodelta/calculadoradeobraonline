// Mostrar/ocultar campo de tamanho personalizado
document.getElementById("tamanhoPiso").addEventListener("change", function() {
    const customField = document.getElementById("customSize");
    if (this.value === "custom") {
        customField.style.display = "block";
    } else {
        customField.style.display = "none";
    }
});

function calcular() {
    const comprimento = parseFloat(document.getElementById("comprimento").value);
    const largura = parseFloat(document.getElementById("largura").value);
    const tamanhoPiso = document.getElementById("tamanhoPiso").value;

    if (!comprimento || !largura) {
        document.getElementById("resultado").innerHTML = "<p style='color: red;'>Preencha todos os campos obrigatórios.</p>";
        return;
    }

    // Calcula área total do ambiente
    const areaTotal = comprimento * largura;

    let areaPeca = 0;
    let dimensaoPeca = "";

    // Define área da peça baseado no tamanho selecionado
    if (tamanhoPiso === "custom") {
        const pisoComp = parseFloat(document.getElementById("pisoComprimento").value);
        const pisoLarg = parseFloat(document.getElementById("pisoLargura").value);
        
        if (!pisoComp || !pisoLarg) {
            document.getElementById("resultado").innerHTML = "<p style='color: red;'>Preencha as dimensões personalizadas do piso.</p>";
            return;
        }
        
        areaPeca = (pisoComp / 100) * (pisoLarg / 100); // Converte cm para m
        dimensaoPeca = `${pisoComp}x${pisoLarg} cm`;
    } else {
        const tamanho = parseInt(tamanhoPiso);
        areaPeca = (tamanho / 100) * (tamanho / 100); // Converte cm para m
        dimensaoPeca = `${tamanho}x${tamanho} cm`;
    }

    // Calcula quantidade de peças
    const pecasNecessarias = Math.ceil(areaTotal / areaPeca);
    const pecasComPerda = Math.ceil(pecasNecessarias * 1.10); // 10% de perda

    // Cálculo de caixas (considerando 2m² por caixa em média)
    const caixasEstimadas = Math.ceil((areaTotal * 1.10) / 2);

    // Estimativa de argamassa colante (5kg por m²)
    const argamassa = (areaTotal * 5).toFixed(1);
    const sacosArgamassa = Math.ceil(argamassa / 20); // Sacos de 20kg

    // Estimativa de rejunte (aproximadamente 1kg por 5m²)
    const rejunte = (areaTotal / 5).toFixed(1);

    document.getElementById("resultado").innerHTML = `
        <h3>Resultado:</h3>
        <p><strong>Área total:</strong> ${areaTotal.toFixed(2)} m²</p>
        <p><strong>Tamanho da peça:</strong> ${dimensaoPeca}</p>
        <p><strong>Peças necessárias:</strong> ${pecasNecessarias} unidades</p>
        <p><strong>Peças com 10% de perda:</strong> ${pecasComPerda} unidades</p>
        <p><strong>Caixas estimadas:</strong> ${caixasEstimadas} caixas (aprox. 2m²/caixa)</p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #DDD;">
        <p><strong>Materiais complementares:</strong></p>
        <p>• <strong>Argamassa colante:</strong> ${argamassa}kg (${sacosArgamassa} sacos de 20kg)</p>
        <p>• <strong>Rejunte:</strong> Aproximadamente ${rejunte}kg</p>
        <p style="font-size: 14px; color: #666; margin-top: 15px;">
        💡 <strong>Dica:</strong> Para ambientes com muitos recortes (banheiros, cozinhas), considere comprar 15-20% a mais.
        </p>
    `;
}
