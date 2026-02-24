document.addEventListener("DOMContentLoaded", () => {
	const toggleButton = document.querySelector(".menu-toggle");
	const overlay = document.querySelector(".menu-overlay");
	const nav = document.querySelector("header nav");

	if (!toggleButton || !overlay || !nav) {
		return;
	}

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
});