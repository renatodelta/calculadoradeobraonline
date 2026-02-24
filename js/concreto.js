function calcular() {
    const area = parseFloat(document.getElementById("area").value);
    const espessuraCm = parseFloat(document.getElementById("espessura").value);

    if (!area || !espessuraCm) {
        document.getElementById("resultado").innerHTML = "Preencha todos os campos.";
        return;
    }

    const espessuraM = espessuraCm / 100;
    const volume = area * espessuraM;

    const cimento = volume * 7;
    const areia = volume * 0.5;
    const brita = volume * 0.8;

    document.getElementById("resultado").innerHTML = `
        <h3>Resultado:</h3>
        <p><strong>Volume total:</strong> ${volume.toFixed(2)} m³</p>
        <p><strong>Cimento:</strong> ${cimento.toFixed(0)} sacos de 50kg</p>
        <p><strong>Areia:</strong> ${areia.toFixed(2)} m³</p>
        <p><strong>Brita:</strong> ${brita.toFixed(2)} m³</p>
    `;
}