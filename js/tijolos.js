function calcular() {
    const area = parseFloat(document.getElementById("area").value);
    const tipoTijolo = document.getElementById("tipoTijolo").value;
    const rejunte = parseFloat(document.getElementById("rejunte").value);

    if (!area || !rejunte) {
        document.getElementById("resultado").innerHTML = "<p style='color: red;'>Preencha todos os campos.</p>";
        return;
    }

    let tijolosPorM2 = 0;
    let nomeTijolo = "";
    
    // Define quantidade de tijolos por m² baseado no tipo
    switch(tipoTijolo) {
        case "8":
            tijolosPorM2 = 25;
            nomeTijolo = "Tijolo baiano 8 furos";
            break;
        case "6":
            tijolosPorM2 = 20;
            nomeTijolo = "Tijolo 6 furos";
            break;
        case "comum":
            tijolosPorM2 = 85;
            nomeTijolo = "Tijolo comum maciço";
            break;
        case "bloco":
            tijolosPorM2 = 12.5;
            nomeTijolo = "Bloco de concreto";
            break;
    }

    // Ajuste baseado na espessura do rejunte (quanto mais espesso, menos tijolos)
    const fatorRejunte = 1 - ((rejunte - 1) * 0.02);
    tijolosPorM2 = tijolosPorM2 * fatorRejunte;

    const totalTijolos = Math.ceil(area * tijolosPorM2);
    const totalComPerda = Math.ceil(totalTijolos * 1.10); // 10% de perda

    // Estimativa de argamassa (aproximadamente 0.05m³ por m² de parede)
    const argamassa = (area * 0.05).toFixed(2);

    document.getElementById("resultado").innerHTML = `
        <h3>Resultado:</h3>
        <p><strong>Tipo escolhido:</strong> ${nomeTijolo}</p>
        <p><strong>Área total:</strong> ${area.toFixed(2)} m²</p>
        <p><strong>Tijolos necessários:</strong> ${totalTijolos} unidades</p>
        <p><strong>Tijolos com 10% de perda:</strong> ${totalComPerda} unidades</p>
        <p><strong>Argamassa estimada:</strong> ${argamassa} m³</p>
        <p style="font-size: 14px; color: #666; margin-top: 15px;">
        💡 <strong>Dica:</strong> Sempre compre 10-15% a mais para compensar quebras e perdas durante o transporte e assentamento.
        </p>
    `;
}
