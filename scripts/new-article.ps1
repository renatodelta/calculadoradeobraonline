param(
    [Parameter(Mandatory = $true)]
    [string]$Slug,

    [Parameter(Mandatory = $true)]
    [string]$Title,

    [Parameter(Mandatory = $true)]
    [string]$Description,

    [string]$Category = "Guia de Obra",
    [int]$ReadTime = 0,
    [string]$Subtitle,
    [string]$OgImageFile,
    [string]$PublishDate,
    [string]$CtaCalculator,
    [string]$CtaTitle,
    [string]$CtaText,
    [string]$CtaButtonText,
    [switch]$Force,
    [switch]$DryRun
)

$ErrorActionPreference = "Stop"

function Escape-Xml {
    param([string]$Text)

    if ([string]::IsNullOrWhiteSpace($Text)) {
        return ""
    }

    return $Text.Replace("&", "&amp;").Replace("<", "&lt;").Replace(">", "&gt;").Replace('"', "&quot;").Replace("'", "&apos;")
}

function Assert-Condition {
    param(
        [bool]$Condition,
        [string]$Message
    )

    if (-not $Condition) {
        throw $Message
    }
}

function Get-ShortText {
    param(
        [string]$Text,
        [int]$MaxLength = 145
    )

    if ($Text.Length -le $MaxLength) {
        return $Text
    }

    return ($Text.Substring(0, $MaxLength - 1).TrimEnd() + "…")
}

function Get-WordCountFromHtml {
    param([string]$Html)

    if ([string]::IsNullOrWhiteSpace($Html)) {
        return 0
    }

    $textOnly = [regex]::Replace($Html, '<[^>]+>', ' ')
    $textOnly = [regex]::Replace($textOnly, '&[a-zA-Z#0-9]+;', ' ')
    $textOnly = [regex]::Replace($textOnly, '\s+', ' ').Trim()

    if ([string]::IsNullOrWhiteSpace($textOnly)) {
        return 0
    }

    return ([regex]::Matches($textOnly, '\b\S+\b')).Count
}

function Render-Template {
    param(
        [string]$Template,
        [hashtable]$Replacements
    )

    $rendered = $Template
    foreach ($key in $Replacements.Keys) {
        $rendered = $rendered.Replace($key, $Replacements[$key])
    }

    return $rendered
}

function Get-TitleLines {
    param([string]$Text)

    $normalized = ($Text -replace "\s+", " ").Trim()
    if ($normalized.Length -le 34) {
        return @($normalized)
    }

    $words = $normalized.Split(" ")
    $line1 = ""
    $line2 = ""

    foreach ($word in $words) {
        if (($line1 + " " + $word).Trim().Length -le 30) {
            $line1 = ($line1 + " " + $word).Trim()
        }
        else {
            $line2 = ($line2 + " " + $word).Trim()
        }
    }

    if ([string]::IsNullOrWhiteSpace($line2)) {
        return @($line1)
    }

    if ($line2.Length -gt 34) {
        $line2 = Get-ShortText -Text $line2 -MaxLength 34
    }

    return @($line1, $line2)
}

function Write-Utf8NoBom {
    param(
        [string]$Path,
        [string]$Content
    )

    $encoding = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($Path, $Content, $encoding)
}

function Resolve-CtaCalculator {
    param(
        [string]$TitleText,
        [string]$DescriptionText
    )

    $theme = ("$TitleText $DescriptionText").ToLowerInvariant()

    if ($theme -match "argamassa|reboco|chapisco|emboco|emboço|assentamento") { return "reboco" }
    if ($theme -match "tijolo|tijolos|alvenaria|bloco|parede") { return "tijolos" }
    if ($theme -match "piso|ceramica|cerâmica|revestimento|porcelanato") { return "piso" }
    if ($theme -match "concreto|cimento|brita|laje|viga|pilar|funda") { return "concreto" }

    return "concreto"
}

function Resolve-CtaDefaults {
    param([string]$Calculator)

    switch ($Calculator) {
        "tijolos" {
            return @{
                Title = "Quer calcular blocos e peças agora?"
                Text = "Use a calculadora de tijolos para estimar quantidades com margem de seguranca."
                Button = "Abrir Calculadora de Tijolos"
            }
        }
        "piso" {
            return @{
                Title = "Quer estimar pisos com precisao?"
                Text = "Use a calculadora de piso para simular area, perdas e quantidade de pecas."
                Button = "Abrir Calculadora de Piso"
            }
        }
        "reboco" {
            return @{
                Title = "Quer calcular argamassa na prática?"
                Text = "Use a calculadora de reboco para estimar volume e materiais de forma rápida."
                Button = "Abrir Calculadora de Reboco"
            }
        }
        default {
            return @{
                Title = "Quer estimar materiais do concreto?"
                Text = "Use a calculadora de concreto para estimar cimento, areia e brita com rapidez."
                Button = "Abrir Calculadora de Concreto"
            }
        }
    }
}

$workspaceRoot = Split-Path -Parent $PSScriptRoot
$artigosDir = Join-Path $workspaceRoot "artigos"
$templatePath = Join-Path $artigosDir "template-artigo.html"
$indexPath = Join-Path $artigosDir "index.html"
$sitemapPath = Join-Path $workspaceRoot "sitemap.xml"
$assetsDir = Join-Path $workspaceRoot "assets"

Assert-Condition -Condition ($Slug -match '^[a-z0-9]+(?:-[a-z0-9]+)*$') -Message "Slug inválido. Use apenas letras minúsculas, números e hífen (ex: como-calcular-areia)."
Assert-Condition -Condition ($ReadTime -ge 0) -Message "ReadTime não pode ser negativo."
Assert-Condition -Condition (Test-Path $templatePath) -Message "Template não encontrado em artigos/template-artigo.html"
Assert-Condition -Condition (Test-Path $indexPath) -Message "Índice de artigos não encontrado em artigos/index.html"
Assert-Condition -Condition (Test-Path $sitemapPath) -Message "sitemap.xml não encontrado"

$articlePath = Join-Path $artigosDir "$Slug.html"
if ((Test-Path $articlePath) -and -not $Force) {
    throw "Já existe um artigo com esse slug: $Slug"
}

$subtitleSafe = if ([string]::IsNullOrWhiteSpace($Subtitle)) { $Description } else { $Subtitle }
$ogImageBase = if ([string]::IsNullOrWhiteSpace($OgImageFile)) { "og-$Slug" } else { $OgImageFile }
$ogImageFile = "$ogImageBase.svg"
$ogImagePath = Join-Path $assetsDir $ogImageFile

$titleSeo = $Title.Trim()
$h1 = ($Title -split "\|")[0].Trim()
if ([string]::IsNullOrWhiteSpace($h1)) { $h1 = $titleSeo }

if ([string]::IsNullOrWhiteSpace($CtaCalculator)) {
    $CtaCalculator = Resolve-CtaCalculator -TitleText $h1 -DescriptionText $Description
}

$ctaDefaults = Resolve-CtaDefaults -Calculator $CtaCalculator
if ([string]::IsNullOrWhiteSpace($CtaTitle)) { $CtaTitle = $ctaDefaults.Title }
if ([string]::IsNullOrWhiteSpace($CtaText)) { $CtaText = $ctaDefaults.Text }
if ([string]::IsNullOrWhiteSpace($CtaButtonText)) { $CtaButtonText = $ctaDefaults.Button }

$publishDateObj = if ([string]::IsNullOrWhiteSpace($PublishDate)) {
    Get-Date
}
else {
    [datetime]::Parse($PublishDate)
}

$dateIso = $publishDateObj.ToString("yyyy-MM-dd")
$dateBr = $publishDateObj.ToString("dd/MM/yyyy")
$isoDateTime = "$dateIso`T09:00:00-03:00"

$keyword = $h1.ToLowerInvariant()
$intro1 = "Se voce quer entender $keyword sem depender de chute, este guia organiza o assunto de forma pratica e aplicavel no canteiro. O objetivo aqui e tirar duvidas que normalmente causam retrabalho: qual referencia adotar, como transformar medida em quantidade real e como registrar as premissas para nao perder o controle. Quando essa etapa e bem feita, a obra ganha previsibilidade de custo, melhora o cronograma e reduz risco de falta de material no momento critico da execucao."
$intro2 = $Description
$intro3 = "Ao final, voce tera um roteiro completo com criterios tecnicos, exemplo aplicado, pontos de verificacao e um checklist objetivo para compra e execucao. A proposta e que o conteudo sirva tanto para quem esta iniciando quanto para quem ja executa obra e quer padronizar decisao. Assim, cada etapa fica documentada, comparavel e facil de revisar antes da contratacao de equipe, pedido de materiais e liberacao do servico."

$section1Title = "Conceitos essenciais"
$section2Title = "Passo a passo de calculo"
$section3Title = "Exemplo pratico"
$section4Title = "Erros comuns e como evitar"
$section5Title = "FAQ rapido"

$section1Paragraph = "Antes de iniciar qualquer calculo relacionado a $keyword, estruture uma base de informacoes minima: finalidade do servico, area ou volume real, condicoes de aplicacao e padrao de acabamento esperado. Essa definicao evita comparacoes erradas entre referencias tecnicas e protege o orcamento de variacoes nao previstas. Outro ponto essencial e registrar origem dos coeficientes usados, pois isso permite revisar o calculo depois, explicar decisao para a equipe e ajustar rapidamente quando houver mudanca de escopo, projeto ou metodo de execucao."
$point1 = "Padronize unidade e criterio desde o primeiro rascunho ate a planilha final, evitando mistura entre m, m2, m3, litro, saco e peca no mesmo calculo."
$point2 = "Inclua perdas tecnicas e operacionais de forma consciente, considerando transporte interno, recortes, retrabalho e variacao de desempenho entre equipes."
$point3 = "Confirme premissas com profissional responsavel e alinhe o metodo de medicao com quem vai comprar, receber e aplicar o material na obra."

$section2Paragraph = "Para transformar medicao em quantidade confiavel, use uma sequencia unica em todas as frentes: medir corretamente, aplicar consumo tecnico compativel com o servico, converter para unidade de compra e adicionar margem de seguranca baseada no contexto real da obra. Esse fluxo reduz erro humano porque cria um padrao de verificacao antes da compra. Sempre que possivel, compare o resultado com historico de obras parecidas e ajuste o coeficiente quando houver diferenca de material, equipe, clima ou metodo executivo, mantendo rastreabilidade da decisao."
$subsectionTitle = "Formula base"
$formulaText = "Quantidade total = medida principal x consumo medio tecnico x fator de seguranca. Na pratica, o fator de seguranca pode variar por complexidade do servico, nivel de recorte, experiencia da equipe e condicoes de armazenamento. Em vez de aplicar um numero fixo para tudo, vale classificar a atividade por risco baixo, medio ou alto e usar margens coerentes para cada caso. Essa simples classificacao melhora previsibilidade de compra e diminui sobra desnecessaria no fechamento da obra."

$step1 = "Meça area ou volume real da aplicacao, registre em planilha e marque as premissas de medicao (descontos, recortes, espessuras, trechos nao executados)."
$step2 = "Aplique consumo tecnico por tipo de material e servico, conferindo se a referencia usada corresponde ao mesmo metodo executivo da sua equipe."
$step3 = "Converta para unidade de compra, adicione margem de seguranca coerente com o risco do servico e gere uma lista final pronta para cotacao."

$section4Text = "Os erros mais comuns em $keyword aparecem quando o calculo e feito com pressa e sem padrao: consumo medio escolhido sem criterio, unidade misturada no meio da conta, perda ignorada e arredondamento incoerente com a forma de compra. Outro erro recorrente e desconsiderar variacao de produtividade entre equipes e condicoes diferentes de execucao, como acesso dificil, clima instavel e mudanca de frente de trabalho. Para evitar esse problema, adote checklist de entrada, revisao antes da compra e controle de consumo real durante a execucao, ajustando a referencia para os proximos lotes."
$section5Text = "FAQ rapido: 1) Qual margem usar? Em servicos simples e bem controlados, comece com 5%; em cenarios com recortes, retrabalho ou logistica complexa, use 8% a 12%. 2) Posso aplicar a mesma media para qualquer obra? Nao. Mudam material, metodo, equipe e acabamento, entao o consumo precisa ser contextualizado. 3) Vale arredondar para cima? Sim, mas com criterio de lote e armazenamento, evitando compra excessiva. 4) Quando revisar o calculo? Sempre que houver alteracao de escopo, mudanca de equipe ou diferenca relevante entre previsto e consumido."

$conclusion1 = "Dominar $keyword melhora decisao de compra, reduz retrabalho e protege o orcamento contra variacoes que normalmente aparecem na fase de execucao."
$conclusion2 = "Com metodo padronizado, registro de premissas e revisao tecnica em cada etapa, voce ganha previsibilidade de custo, ritmo de obra mais estavel e mais seguranca para executar sem interrupcoes."

$template = Get-Content -Path $templatePath -Raw -Encoding UTF8

$replacements = @{
    "[TITULO SEO]" = $titleSeo
    "[SUBTITULO CURTO]" = "Guia Pratico"
    "[DESCRICAO SEO COM FOCO NA PALAVRA-CHAVE]" = $Description
    "[DESCRICAO OPEN GRAPH]" = $Description
    "[DESCRICAO TWITTER]" = $Description
    "[SLUG]" = $Slug
    "[OG_IMAGE_FILE]" = $ogImageBase
    "[YYYY-MM-DD]" = $dateIso
    "[H1 DO ARTIGO]" = $h1
    "[DESCRICAO RESUMIDA DO ARTIGO]" = $Description
    "[CATEGORIA DO GUIA]" = $Category
    "[DD/MM/AAAA]" = $dateBr
    "[X]" = $ReadTime
    "[SUBTITULO CURTO COM BENEFICIO PRINCIPAL]" = $subtitleSafe
    "[ABERTURA COM DOR DO USUARIO E PALAVRA-CHAVE PRINCIPAL]" = $intro1
    "[CONTEXTO E IMPORTANCIA PRATICA]" = $intro2
    "[PROMESSA DO QUE O LEITOR VAI APRENDER]" = $intro3
    "[SECAO 1]" = $section1Title
    "[SECAO 2]" = $section2Title
    "[SECAO 3]" = $section3Title
    "[SECAO 4]" = $section4Title
    "[SECAO 5]" = $section5Title
    "[TITULO DA SECAO 1]" = $section1Title
    "[PARAGRAFO DA SECAO 1]" = $section1Paragraph
    "[PONTO IMPORTANTE 1]" = $point1
    "[PONTO IMPORTANTE 2]" = $point2
    "[PONTO IMPORTANTE 3]" = $point3
    "[TITULO DA SECAO 2]" = $section2Title
    "[PARAGRAFO DA SECAO 2]" = $section2Paragraph
    "[SUBSECAO]" = $subsectionTitle
    "[EXEMPLO OU FORMULA]" = $formulaText
    "[TITULO DA SECAO 3]" = $section3Title
    "[PASSO 1]" = $step1
    "[PASSO 2]" = $step2
    "[PASSO 3]" = $step3
    "[TITULO DA SECAO 4]" = $section4Title
    "[CONTEUDO DA SECAO 4]" = $section4Text
    "[TITULO DA SECAO 5]" = $section5Title
    "[CONTEUDO DA SECAO 5]" = $section5Text
    "[RESUMO FINAL COM PALAVRA-CHAVE PRINCIPAL]" = $conclusion1
    "[ORIENTACAO PRATICA FINAL PARA O USUARIO]" = $conclusion2
    "[CHAMADA DE ACAO]" = $CtaTitle
    "[TEXTO DE APOIO DA ACAO]" = $CtaText
    "[CALCULADORA-DESTINO]" = $CtaCalculator
    "[TEXTO DO BOTAO]" = $CtaButtonText
}

$targetWordCount = 1000
$minWordCount = 900
$maxExpansionAttempts = 3
$expansionAttempts = 0
$articleWordCount = 0

$section4Expansion = "Como rotina de controle, compare o previsto com o realizado em ciclos curtos, documente diferencas e atualize coeficientes com base em dados da propria obra. Esse habito transforma estimativa em processo de melhoria continua e reduz surpresas financeiras no fechamento."
$section5Expansion = "5) Como escolher referencia confiavel? Priorize fonte tecnica, histórico interno e validacao em campo. 6) Preciso registrar justificativa de ajuste? Sim, porque isso evita retrabalho analitico e melhora comunicacao entre planejamento, compras e execucao."

do {
    $replacements["[CONTEUDO DA SECAO 4]"] = $section4Text
    $replacements["[CONTEUDO DA SECAO 5]"] = $section5Text
    $replacements["[ORIENTACAO PRATICA FINAL PARA O USUARIO]"] = $conclusion2

    $articleHtml = Render-Template -Template $template -Replacements $replacements
    $articleWordCount = Get-WordCountFromHtml -Html $articleHtml

    if ($articleWordCount -lt $minWordCount -and $expansionAttempts -lt $maxExpansionAttempts) {
        $section4Text += " " + $section4Expansion
        $section5Text += " " + $section5Expansion
        $conclusion2 += " Revise o resultado com a equipe antes da compra e mantenha um controle simples de consumo real por etapa para calibrar os proximos calculos."
    }

    $expansionAttempts++
}
while ($articleWordCount -lt $minWordCount -and $expansionAttempts -le $maxExpansionAttempts)

if ($articleWordCount -lt $minWordCount) {
    Write-Warning "Artigo gerado com $articleWordCount palavras (abaixo da meta de ~1000)."
}
else {
    Write-Host "- Texto estimado: $articleWordCount palavras" -ForegroundColor DarkCyan
}

if ($ReadTime -le 0) {
    $ReadTime = [Math]::Max(5, [int][Math]::Ceiling($articleWordCount / 190.0))
}

$replacements["[X]"] = $ReadTime
$articleHtml = Render-Template -Template $template -Replacements $replacements

$placeholderMatches = [regex]::Matches($articleHtml, '\[[A-Z][A-Z0-9 \-/]{2,}\]')
if ($placeholderMatches.Count -gt 0) {
    $pending = ($placeholderMatches | ForEach-Object { $_.Value } | Select-Object -Unique) -join ", "
    Write-Warning "Ainda existem placeholders no artigo gerado: $pending"
}

$titleLines = Get-TitleLines -Text $h1
$titleEscaped = Escape-Xml -Text $h1
$line1 = Escape-Xml -Text $titleLines[0]
$line2 = if ($titleLines.Count -gt 1) { Escape-Xml -Text $titleLines[1] } else { "" }
$subtitleLine = Escape-Xml -Text (Get-ShortText -Text $subtitleSafe -MaxLength 56)

$svgLine2 = if ([string]::IsNullOrWhiteSpace($line2)) {
    ""
}
else {
@"
  <text x="96" y="340" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="58" font-weight="700">
    $line2
  </text>
"@
}

$ogSvg = @"
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-labelledby="title desc">
    <title id="title">$titleEscaped</title>
  <desc id="desc">Imagem social do artigo $line1</desc>
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#05273d"/>
      <stop offset="100%" stop-color="#083552"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bg)"/>
  <rect x="64" y="64" width="1072" height="502" rx="24" fill="#ffffff" opacity="0.08"/>

  <text x="96" y="190" fill="#b8d8ec" font-family="Inter, Arial, sans-serif" font-size="30" font-weight="700" letter-spacing="2">
    GUIA DA OBRA
  </text>

  <text x="96" y="270" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-size="58" font-weight="700">
    $line1
  </text>
$svgLine2
  <text x="96" y="430" fill="#d1e5f3" font-family="Inter, Arial, sans-serif" font-size="32" font-weight="500">
    $subtitleLine
  </text>

  <text x="96" y="520" fill="#ffb27f" font-family="Inter, Arial, sans-serif" font-size="28" font-weight="700">
    calculadoradeobraonline.com.br
  </text>
</svg>
"@

$excerpt = Get-ShortText -Text $Description -MaxLength 145
$card = @"
        <a class="blog-post-card" href="$Slug.html" aria-label="Ler artigo: $h1">
            <p class="blog-post-tag">$Category</p>
            <h3>$h1</h3>
            <p>$excerpt</p>
            <p class="blog-post-meta">Publicado em $dateBr - Leitura de $ReadTime min</p>
        </a>
"@

$indexHtml = Get-Content -Path $indexPath -Raw -Encoding UTF8
if ($indexHtml -match [regex]::Escape("href=`"$Slug.html`"")) {
    throw "O artigo já está listado em artigos/index.html"
}

$patternTodos = '(?s)(<h2>Todos os artigos</h2>\s*)'
$newIndexHtml = [regex]::Replace($indexHtml, $patternTodos, "`$1`r`n$card`r`n", 1)
Assert-Condition -Condition ($newIndexHtml -ne $indexHtml) -Message "Não foi possível inserir o card em artigos/index.html (bloco 'Todos os artigos' não encontrado)."

$sitemapXml = Get-Content -Path $sitemapPath -Raw -Encoding UTF8
$articleUrl = "https://calculadoradeobraonline.com.br/artigos/$Slug.html"
if ($sitemapXml -match [regex]::Escape($articleUrl)) {
    throw "A URL já existe no sitemap.xml"
}

$articleEntry = @"
  <url>
    <loc>$articleUrl</loc>
    <lastmod>$dateIso</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
"@

$updatedSitemap = $sitemapXml -replace '</urlset>', "$articleEntry`r`n</urlset>"
Assert-Condition -Condition ($updatedSitemap -ne $sitemapXml) -Message "Não foi possível atualizar sitemap.xml"

$updatedSitemap = [regex]::Replace(
    $updatedSitemap,
    '(<loc>https://calculadoradeobraonline.com.br/artigos/index.html</loc>\s*<lastmod>)([^<]+)(</lastmod>)',
    [System.Text.RegularExpressions.MatchEvaluator]{
        param($match)
        return "$($match.Groups[1].Value)$dateIso$($match.Groups[3].Value)"
    }
)

if (-not $DryRun) {
    Write-Utf8NoBom -Path $articlePath -Content $articleHtml

    if ((-not (Test-Path $ogImagePath)) -or $Force) {
        Write-Utf8NoBom -Path $ogImagePath -Content $ogSvg
    }

    Write-Utf8NoBom -Path $indexPath -Content $newIndexHtml
    Write-Utf8NoBom -Path $sitemapPath -Content $updatedSitemap
}

Write-Host ""
Write-Host "Artigo preparado com sucesso." -ForegroundColor Green
Write-Host "- Arquivo: artigos/$Slug.html"
Write-Host "- OG image: assets/$ogImageFile"
Write-Host "- Índice atualizado: artigos/index.html"
Write-Host "- Sitemap atualizado: sitemap.xml"
Write-Host ""
Write-Host "Comando usado:" -ForegroundColor Cyan
Write-Host "./scripts/new-article.ps1 -Slug '$Slug' -Title '$Title' -Description '$Description'"

if ($DryRun) {
    Write-Host ""
    Write-Host "Dry run ativado: nenhum arquivo foi alterado." -ForegroundColor Yellow
}
