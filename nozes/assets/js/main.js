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

	// Botão "Imprimir / PDF" (viewer de relatório)
	document.querySelectorAll('[data-nz-print]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			window.print();
		});
	});

	// Lightbox das galerias do blog: clica na imagem, abre em tela cheia e navega
	(function () {
		var content = document.querySelector('.nz-single-post__content');
		if (!content) {
			return;
		}

		var images = Array.prototype.slice.call(
			content.querySelectorAll('.wp-block-gallery img, .gallery img')
		);
		if (!images.length) {
			return;
		}

		// Descobre a maior versão disponível de cada imagem (link para o arquivo,
		// srcset ou src) e a legenda (figcaption/legenda da galeria ou alt).
		var slides = images.map(function (img) {
			var full = img.getAttribute('src');
			var link = img.closest('a');
			if (link && /\.(jpe?g|png|gif|webp|avif)(\?|$)/i.test(link.getAttribute('href') || '')) {
				full = link.getAttribute('href');
			} else if (img.getAttribute('data-full-url')) {
				full = img.getAttribute('data-full-url');
			} else if (img.currentSrc) {
				full = img.currentSrc;
			}

			var caption = '';
			var figure = img.closest('figure');
			if (figure) {
				var cap = figure.querySelector('figcaption');
				if (cap) {
					caption = cap.textContent.trim();
				}
			}
			if (!caption) {
				var item = img.closest('.gallery-item');
				if (item) {
					var gcap = item.querySelector('.gallery-caption');
					if (gcap) {
						caption = gcap.textContent.trim();
					}
				}
			}
			if (!caption) {
				caption = img.getAttribute('alt') || '';
			}
			return { src: full, caption: caption };
		});

		// Monta a estrutura do lightbox uma única vez.
		var box = document.createElement('div');
		box.className = 'nz-lightbox';
		box.setAttribute('role', 'dialog');
		box.setAttribute('aria-modal', 'true');
		box.setAttribute('aria-hidden', 'true');
		box.innerHTML =
			'<button class="nz-lightbox__close" type="button" aria-label="Fechar">×</button>' +
			'<button class="nz-lightbox__nav nz-lightbox__nav--prev" type="button" aria-label="Anterior">‹</button>' +
			'<figure class="nz-lightbox__figure">' +
				'<img class="nz-lightbox__img" src="" alt="">' +
				'<figcaption class="nz-lightbox__caption"></figcaption>' +
				'<span class="nz-lightbox__count"></span>' +
			'</figure>' +
			'<button class="nz-lightbox__nav nz-lightbox__nav--next" type="button" aria-label="Próxima">›</button>';
		document.body.appendChild(box);

		var imgEl = box.querySelector('.nz-lightbox__img');
		var capEl = box.querySelector('.nz-lightbox__caption');
		var countEl = box.querySelector('.nz-lightbox__count');
		var prevBtn = box.querySelector('.nz-lightbox__nav--prev');
		var nextBtn = box.querySelector('.nz-lightbox__nav--next');
		var closeBtn = box.querySelector('.nz-lightbox__close');
		var current = 0;
		var multiple = slides.length > 1;

		prevBtn.hidden = !multiple;
		nextBtn.hidden = !multiple;

		function render() {
			var slide = slides[current];
			imgEl.setAttribute('src', slide.src);
			imgEl.setAttribute('alt', slide.caption);
			capEl.textContent = slide.caption;
			capEl.style.display = slide.caption ? '' : 'none';
			countEl.textContent = multiple ? (current + 1) + ' / ' + slides.length : '';
		}

		function open(index) {
			current = index;
			render();
			box.classList.add('is-open');
			box.setAttribute('aria-hidden', 'false');
			document.body.classList.add('nz-lightbox-open');
		}

		function close() {
			box.classList.remove('is-open');
			box.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('nz-lightbox-open');
		}

		function go(step) {
			current = (current + step + slides.length) % slides.length;
			render();
		}

		images.forEach(function (img, i) {
			var trigger = img.closest('a') || img;
			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				open(i);
			});
		});

		prevBtn.addEventListener('click', function () { go(-1); });
		nextBtn.addEventListener('click', function () { go(1); });
		closeBtn.addEventListener('click', close);
		box.addEventListener('click', function (e) {
			if (e.target === box || e.target.classList.contains('nz-lightbox__figure')) {
				close();
			}
		});
		document.addEventListener('keydown', function (e) {
			if (!box.classList.contains('is-open')) {
				return;
			}
			if (e.key === 'Escape') {
				close();
			} else if (multiple && e.key === 'ArrowLeft') {
				go(-1);
			} else if (multiple && e.key === 'ArrowRight') {
				go(1);
			}
		});
	})();
})();
