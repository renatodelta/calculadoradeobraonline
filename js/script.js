// Execute theme restoration immediately to avoid style flashing
(function() {
    const savedTheme = localStorage.getItem("theme") || 
        (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
    document.documentElement.setAttribute("data-theme", savedTheme);
})();

document.addEventListener("DOMContentLoaded", () => {
    // Menu mobile logic
    const toggleButton = document.querySelector(".menu-toggle");
    const overlay = document.querySelector(".menu-overlay");
    const nav = document.querySelector("header nav");

    if (toggleButton && overlay && nav) {
        const closeMenu = () => {
            document.body.classList.remove("menu-open");
            toggleButton.setAttribute("aria-expanded", "false");
        };

        const openMenu = () => {
            document.body.classList.add("menu-open");
            toggleButton.setAttribute("aria-expanded", "true");
        };

        toggleButton.addEventListener("click", () => {
            if (document.body.classList.contains("menu-open")) {
                closeMenu();
                return;
            }
            openMenu();
        });

        overlay.addEventListener("click", closeMenu);

        nav.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", closeMenu);
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeMenu();
            }
        });
    }

    // Theme Switcher button injection and handling
    const setupThemeToggle = () => {
        const navContainer = document.querySelector("header nav");
        if (!navContainer) return;

        // Check if theme button already exists
        if (document.querySelector(".theme-toggle-btn")) return;

        const themeBtn = document.createElement("button");
        themeBtn.type = "button";
        themeBtn.className = "theme-toggle-btn";
        themeBtn.setAttribute("aria-label", "Alternar tema escuro/claro");
        themeBtn.innerHTML = `
            <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        `;

        navContainer.appendChild(themeBtn);

        themeBtn.addEventListener("click", () => {
            const currentTheme = document.documentElement.getAttribute("data-theme") || "light";
            const newTheme = currentTheme === "light" ? "dark" : "light";
            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
        });
    };

    setupThemeToggle();

    // Technical Report Clipboard Copying Logic
    const addCopyButton = (resultDiv) => {
        if (resultDiv.querySelector(".btn-copy-resultado")) return;
        if (resultDiv.innerHTML.trim() === "" || resultDiv.textContent.trim() === "") return;

        const copyBtn = document.createElement("button");
        copyBtn.type = "button";
        copyBtn.className = "btn-secondary btn-copy-resultado";
        copyBtn.style.marginTop = "20px";
        copyBtn.style.width = "100%";
        copyBtn.style.gap = "8px";
        copyBtn.style.display = "inline-flex";
        copyBtn.style.alignItems = "center";
        copyBtn.style.justifyContent = "center";
        copyBtn.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
            <span>Copiar Resumo dos Materiais</span>
        `;

        copyBtn.addEventListener("click", () => {
            const tempDiv = resultDiv.cloneNode(true);
            const btn = tempDiv.querySelector(".btn-copy-resultado");
            if (btn) btn.remove();
            
            // Clean up text representation for easy sharing
            let text = tempDiv.innerText || tempDiv.textContent;
            text = text.replace(/💡/g, '\n💡');
            text = text.replace(/•/g, '\n•');
            text = text.replace(/Resultado:/g, '*Resultado do Cálculo* 🏗️\n');
            text = text.replace(/\n\s*\n/g, '\n').trim();
            text += "\n\nCalculadora de Obra Online 🔗 https://calculadoradeobraonline.com.br";

            navigator.clipboard.writeText(text).then(() => {
                const span = copyBtn.querySelector("span");
                const originalText = span.textContent;
                span.textContent = "Copiado para a área de transferência! ✓";
                copyBtn.classList.add("copied");
                setTimeout(() => {
                    span.textContent = originalText;
                    copyBtn.classList.remove("copied");
                }, 2000);
            }).catch(err => {
                console.error("Erro ao copiar dados: ", err);
            });
        });

        resultDiv.appendChild(copyBtn);
    };

    // Watch for dynamic calculation result updates
    const setupResultObserver = () => {
        const targetIds = ["resultado", "resultado-agua-obra"];
        targetIds.forEach(id => {
            const target = document.getElementById(id);
            if (target) {
                const observer = new MutationObserver(() => {
                    observer.disconnect();
                    addCopyButton(target);
                    observer.observe(target, { childList: true, subtree: true });
                });
                observer.observe(target, { childList: true, subtree: true });
                // Check once initially
                addCopyButton(target);
            }
        });
    };

    setupResultObserver();
});