// Atualizar espessura automaticamente baseado no tipo
document.getElementById("tipoReboco").addEventListener("change", function() {
    const espessuraInput = document.getElementById("espessura");
    const tracoSelect = document.getElementById("traco");
    
    switch(this.value) {
        case "chapisco":
            espessuraInput.value = 0.5;
            tracoSelect.value = "1:3";
            break;
        case "emboco":
            espessuraInput.value = 2;
            tracoSelect.value = "1:4";
            break;
        case "reboco":
            espessuraInput.value = 0.5;
            tracoSelect.value = "1:5";
            break;
        case "completo":
            espessuraInput.value = 3;
            tracoSelect.value = "1:4";
            break;
    }
});

function calcular() {
    const area = parseFloat(document.getElementById("area").value);
    const tipoReboco = document.getElementById("tipoReboco").value;
    const espessura = parseFloat(document.getElementById("espessura").value);
    const traco = document.getElementById("traco").value;

    if (!area || !espessura) {
        document.getElementById("resultado").innerHTML = "<p style='color: red;'>Preencha todos os campos.</p>";
        return;
    }

    // Calcula volume de argamassa em m³
    const volumeArgamassa = area * (espessura / 100);

    let cimento = 0;
    let areia = 0;
    let cal = 0;
    let descricaoTraco = "";

    // Define quantidade de materiais baseado no traço
    switch(traco) {
        case "1:3":
            cimento = volumeArgamassa * 7; // sacos de 50kg
            areia = volumeArgamassa * 0.9; // m³
            descricaoTraco = "1:3 (cimento:areia)";
            break;
        case "1:4":
            cimento = volumeArgamassa * 6;
            areia = volumeArgamassa * 0.95;
            descricaoTraco = "1:4 (cimento:areia)";
            break;
        case "1:5":
            cimento = volumeArgamassa * 5;
            areia = volumeArgamassa * 1.0;
            descricaoTraco = "1:5 (cimento:areia)";
            break;
        case "1:2:6":
            cimento = volumeArgamassa * 4.5;
            cal = volumeArgamassa * 3; // sacos de 20kg
            areia = volumeArgamassa * 1.0;
            descricaoTraco = "1:2:6 (cimento:cal:areia)";
            break;
    }

    // Arredonda valores
    cimento = Math.ceil(cimento);
    areia = areia.toFixed(2);
    cal = Math.ceil(cal);

    // Nome do tipo de reboco
    let nomeReboco = "";
    switch(tipoReboco) {
        case "chapisco":
            nomeReboco = "Chapisco";
            break;
        case "emboco":
            nomeReboco = "Emboço/Massa única";
            break;
        case "reboco":
            nomeReboco = "Reboco fino";
            break;
        case "completo":
            nomeReboco = "Revestimento completo";
            break;
    }

    let resultadoHTML = `
        <h3>Resultado:</h3>
        <p><strong>Tipo de revestimento:</strong> ${nomeReboco}</p>
        <p><strong>Área total:</strong> ${area.toFixed(2)} m²</p>
        <p><strong>Espessura:</strong> ${espessura} cm</p>
        <p><strong>Volume de argamassa:</strong> ${volumeArgamassa.toFixed(3)} m³</p>
        <p><strong>Traço:</strong> ${descricaoTraco}</p>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #DDD;">
        <p><strong>Materiais necessários:</strong></p>
        <p>• <strong>Cimento:</strong> ${cimento} sacos de 50kg</p>
        <p>• <strong>Areia:</strong> ${areia} m³</p>
    `;

    if (cal > 0) {
        resultadoHTML += `<p>• <strong>Cal:</strong> ${cal} sacos de 20kg</p>`;
    }

    resultadoHTML += `
        <p style="font-size: 14px; color: #666; margin-top: 15px;">
        💡 <strong>Dica:</strong> Prepare a argamassa em pequenas quantidades para evitar desperdício. A argamassa deve ser usada em até 2 horas após o preparo.
        </p>
    `;

    document.getElementById("resultado").innerHTML = resultadoHTML;
}
