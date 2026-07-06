(function () {
	'use strict';

	// Menu mobile
	var burger = document.getElementById('nz-burger');
	var nav = document.getElementById('menu-principal');

	if (burger && nav) {
		burger.addEventListener('click', function () {
			var isOpen = nav.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});

		nav.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				nav.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
			});
		});
	}

	// Acordeão de relatórios na Área do Cliente
	document.querySelectorAll('[data-nz-toggle-report]').forEach(function (head) {
		head.addEventListener('click', function () {
			var body = head.nextElementSibling;
			if (!body) {
				return;
			}
			var isHidden = body.hasAttribute('hidden');
			if (isHidden) {
				body.removeAttribute('hidden');
			} else {
				body.setAttribute('hidden', '');
			}
		});
	});
})();
