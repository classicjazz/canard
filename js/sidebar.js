/**
 * @fileoverview Sidebar toggle — shows/hides the #secondary sidebar on narrow viewports
 * and keeps aria-expanded in sync on both the sidebar and button for screen reader compatibility.
 */

(() => {
	const sidebar = document.getElementById("secondary");
	if (!sidebar) {
		return;
	}

	const button = document.querySelector(".sidebar-toggle");
	if (!button) {
		return;
	}

	sidebar.setAttribute("aria-expanded", "false");

	/**
	 * Toggles the sidebar open or closed on button click and synchronizes
	 * aria-expanded on both the sidebar element and the toggle button.
	 *
	 * @returns {void}
	 */
	button.addEventListener("click", () => {
		const expanded = sidebar.classList.toggle("toggled");
		button.classList.toggle("toggled");
		const state = expanded ? "true" : "false";
		sidebar.setAttribute("aria-expanded", state);
		button.setAttribute("aria-expanded", state);
	});
})();
