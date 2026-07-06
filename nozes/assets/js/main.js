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

	// "Mostrar senha" no login da Área do Cliente
	document.querySelectorAll('[data-nz-toggle-password]').forEach(function (checkbox) {
		var target = document.getElementById(checkbox.getAttribute('data-nz-toggle-password'));
		if (!target) {
			return;
		}
		checkbox.addEventListener('change', function () {
			target.type = checkbox.checked ? 'text' : 'password';
		});
	});

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
