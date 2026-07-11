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

	// Antes de imprimir, avisa bibliotecas de gráfico responsivas (Chart.js,
	// ApexCharts, ECharts, etc.) para se redesenharem no tamanho da folha —
	// sem isso é comum o gráfico sair em branco ou cortado no PDF.
	window.addEventListener('beforeprint', function () {
		window.dispatchEvent(new Event('resize'));
	});

	// Busca de relatórios na Área do Cliente: filtra por palavra-chave (não
	// precisa ser o nome/data exatos). Divide o texto em palavras e casa por
	// substring ou prefixo em comum — assim "market", "junho" ou "trimestre"
	// encontram "Marketing Digital", "jun." e "Trimestral".
	(function () {
		var input = document.querySelector('[data-nz-report-search]');
		var scope = document.querySelector('[data-nz-report-scope]');
		if (!input || !scope) {
			return;
		}

		var groups = Array.prototype.slice.call(scope.querySelectorAll('[data-nz-report-group]'));
		var emptyMsg = document.querySelector('[data-nz-report-empty]');
		var queryOut = emptyMsg ? emptyMsg.querySelector('[data-nz-report-query]') : null;

		function normalize(str) {
			return (str || '').toString().toLowerCase()
				.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		}

		function toWords(str) {
			return normalize(str).split(/[^a-z0-9]+/).filter(Boolean);
		}

		function commonPrefix(a, b) {
			var n = Math.min(a.length, b.length);
			var i = 0;
			while (i < n && a.charAt(i) === b.charAt(i)) {
				i++;
			}
			return i;
		}

		function wordMatches(word, token) {
			return word.indexOf(token) !== -1 || commonPrefix(word, token) >= 4;
		}

		// Pré-calcula as palavras de cada relatório uma única vez.
		var items = Array.prototype.slice.call(scope.querySelectorAll('[data-nz-report-item]')).map(function (el) {
			return { el: el, words: toWords(el.getAttribute('data-nz-report-search-text')) };
		});

		function filter() {
			var raw = input.value.trim();
			var tokens = toWords(raw);
			var anyVisible = false;

			items.forEach(function (item) {
				// Casa quando TODAS as palavras digitadas aparecem no relatório.
				var match = tokens.every(function (token) {
					return item.words.some(function (word) {
						return wordMatches(word, token);
					});
				});
				item.el.hidden = !match;
				if (match) {
					anyVisible = true;
				}
			});

			// Esconde categorias/subcategorias que ficaram sem nenhum relatório visível.
			groups.forEach(function (group) {
				var hasVisible = group.querySelector('[data-nz-report-item]:not([hidden])');
				group.hidden = !hasVisible;
			});

			if (emptyMsg) {
				emptyMsg.hidden = anyVisible;
				if (queryOut) {
					queryOut.textContent = raw;
				}
			}
		}

		input.addEventListener('input', filter);
		// Enter não recarrega a página (filtro é instantâneo, não é formulário).
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				filter();
			}
		});
	})();

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
