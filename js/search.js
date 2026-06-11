/**
 * @fileoverview Search form hover/focus styling and header search toggle.
 */

(() => {
	/**
	 * Adds and removes a hover/focus class on each search form when its submit
	 * button is hovered or receives keyboard focus.
	 *
	 * Uses querySelectorAll so every .search-submit on the page (e.g. both a
	 * header search and a widget-area search) receives handlers — querySelector
	 * would only hook the first match, leaving subsequent forms inconsistent.
	 *
	 * @returns {void}
	 */
	window.addEventListener("DOMContentLoaded", () => {
		const searchSubmits = document.querySelectorAll(".search-submit");
		if (!searchSubmits.length) {
			return;
		}

		/**
		 * Adds the 'hover' class to the closest ancestor .search-form.
		 *
		 * @this {HTMLElement}
		 * @returns {void}
		 */
		function searchAddClass() {
			this.closest(".search-form")?.classList.add("hover");
		}

		/**
		 * Removes the 'hover' class from the closest ancestor .search-form.
		 *
		 * @this {HTMLElement}
		 * @returns {void}
		 */
		function searchRemoveClass() {
			this.closest(".search-form")?.classList.remove("hover");
		}

		/**
		 * Attaches hover and focus event handlers to a single search submit button.
		 *
		 * @param {HTMLElement} searchSubmit - The search submit button element.
		 * @returns {void}
		 */
		searchSubmits.forEach((searchSubmit) => {
			searchSubmit.addEventListener("mouseenter", searchAddClass);
			searchSubmit.addEventListener("mouseleave", searchRemoveClass);
			searchSubmit.addEventListener("focus", searchAddClass);
			searchSubmit.addEventListener("blur", searchRemoveClass);
		});
	});
})();

(() => {
	/**
	 * Toggles the header search form open/closed and keeps aria-expanded in sync
	 * on both the button and the form.
	 *
	 * Dismisses the primary navigation panel before opening search so the two
	 * drop-downs never overlap in portrait mode on iPhone / iPad.
	 *
	 * @returns {void}
	 */

	const container = document.getElementById("search-header");
	if (!container) {
		return;
	}

	const button = container.querySelector("button");
	if (!button) {
		return;
	}

	const form = container.querySelector("form");
	if (!form) {
		button.style.display = "none";
		return;
	}
	form.setAttribute("aria-expanded", "false");

	/**
	 * Toggles the header search form open or closed on button click.
	 *
	 * Dismisses the primary navigation panel first if it is open, then toggles
	 * the search container and synchronizes aria-expanded on both the button and the form.
	 *
	 * @returns {void}
	 */
	button.addEventListener("click", () => {
		// Dismiss the nav panel before toggling search so the two drop-downs
		// never overlap in portrait mode on iPhone / iPad.
		const navContainer = document.getElementById("site-navigation");
		if (navContainer && navContainer.classList.contains("toggled")) {
			navContainer.classList.remove("toggled");
			navContainer
				.querySelector("button")
				?.setAttribute("aria-expanded", "false");
			navContainer.querySelector("ul")?.setAttribute("aria-expanded", "false");
		}

		const isToggled = container.classList.contains("toggled");
		document.body.classList.toggle("search-toggled", !isToggled);
		container.classList.toggle("toggled", !isToggled);
		button.setAttribute("aria-expanded", isToggled ? "false" : "true");
		form.setAttribute("aria-expanded", isToggled ? "false" : "true");
	});
})();
