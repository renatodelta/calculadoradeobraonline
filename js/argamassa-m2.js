function calcularArgamassaM2() {
    var area = parseFloat(document.getElementById("area").value);
    var tipoElemento = document.getElementById("tipoElemento").value;
    var junta = parseFloat(document.getElementById("junta").value);
    var traco = document.getElementById("traco").value;
    var perda = parseFloat(document.getElementById("perda").value);
    var resultado = document.getElementById("resultado");

    if (!area || area <= 0 || !junta || junta <= 0 || perda < 0) {
        resultado.innerHTML = "<p style='color: red;'>Preencha os campos com valores válidos.</p>";
        return;
    }

    var consumosBase = {
        ceramico_9x19x19: { consumo: 0.020, nome: "Tijolo cerâmico 9x19x19 cm" },
        ceramico_9x19x29: { consumo: 0.018, nome: "Tijolo cerâmico 9x19x29 cm" },
        bloco_14x19x39: { consumo: 0.016, nome: "Bloco de concreto 14x19x39 cm" },
        bloco_19x19x39: { consumo: 0.021, nome: "Bloco estrutural 19x19x39 cm" }
    };

    var dados = consumosBase[tipoElemento];
    var consumoAjustadoM2 = dados.consumo * (junta / 12);
    var volumeFresco = area * consumoAjustadoM2;
    var volumeComPerda = volumeFresco * (1 + perda / 100);

    var fatorConversaoSeco = 1.33;
    var volumeSeco = volumeComPerda * fatorConversaoSeco;

    var partes = traco.split(":").map(function (item) { return parseFloat(item); });
    var cimentoPartes = partes[0] || 0;
    var calPartes = partes.length === 3 ? partes[1] : 0;
    var areiaPartes = partes.length === 3 ? partes[2] : partes[1];
    var totalPartes = cimentoPartes + calPartes + areiaPartes;

    var volumeCimento = volumeSeco * (cimentoPartes / totalPartes);
    var volumeCal = volumeSeco * (calPartes / totalPartes);
    var volumeAreia = volumeSeco * (areiaPartes / totalPartes);

    var volumeSacoCimento50kg = 0.0347;
    var volumeSacoCal20kg = 0.036;

    var sacosCimento = Math.ceil(volumeCimento / volumeSacoCimento50kg);
    var sacosCal = Math.ceil(volumeCal / volumeSacoCal20kg);

    var consumoLitrosM2 = consumoAjustadoM2 * 1000;

    var html = "";
    html += "<h3>Resultado:</h3>";
    html += "<p><strong>Tipo de alvenaria:</strong> " + dados.nome + "</p>";
    html += "<p><strong>Área total:</strong> " + area.toFixed(2) + " m²</p>";
    html += "<p><strong>Junta média:</strong> " + junta.toFixed(0) + " mm</p>";
    html += "<p><strong>Traço:</strong> " + traco + "</p>";
    html += "<p><strong>Consumo estimado por m²:</strong> " + consumoLitrosM2.toFixed(1) + " litros de argamassa fresca</p>";
    html += "<hr style='margin: 20px 0; border: none; border-top: 1px solid #DDD;'>";
    html += "<p><strong>Argamassa total (fresca):</strong> " + volumeComPerda.toFixed(3) + " m³ (com " + perda.toFixed(0) + "% de perdas)</p>";
    html += "<p><strong>Cimento:</strong> " + sacosCimento + " sacos de 50kg</p>";
    html += "<p><strong>Areia:</strong> " + volumeAreia.toFixed(3) + " m³</p>";

    if (calPartes > 0) {
        html += "<p><strong>Cal:</strong> " + sacosCal + " sacos de 20kg</p>";
    }

    html += "<p style='font-size: 14px; color: #666; margin-top: 15px;'>";
    html += "💡 <strong>Dica:</strong> compare o resultado com o consumo real da equipe nos primeiros panos de alvenaria e ajuste a margem para o restante da obra.";
    html += "</p>";

    resultado.innerHTML = html;
}
