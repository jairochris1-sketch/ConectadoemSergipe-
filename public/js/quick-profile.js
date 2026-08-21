document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-quick-profile]');
    const form = document.querySelector('#quick-profile-form');
    if (!root || !form) return;

    const sections = [...form.querySelectorAll('[data-step]')];
    const tabs = [...document.querySelectorAll('[data-go-step]')];
    const nextButton = document.querySelector('#quick-next-button');
    const backButton = document.querySelector('#quick-back-button');
    const categorySelect = document.querySelector('#category');
    const serviceGrid = document.querySelector('#quick-service-grid');
    const liberalFields = document.querySelector('[data-liberal-fields]');
    const description = document.querySelector('#description');
    const citySearch = document.querySelector('#city_search');
    const photoInput = document.querySelector('#photos');
    const photoLabel = document.querySelector('#quick-photo-label');
    const categories = JSON.parse(root.dataset.categories || '{}');
    const oldServices = Array.isArray(window.quickProfileOldServices) ? window.quickProfileOldServices : [];
    const oldCategory = window.quickProfileOldCategory || '';
    const state = { step: 0, photoUrl: '' };
    const themeButtons = [...document.querySelectorAll('[data-quick-theme-toggle]')];

    function updateThemeButtons() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        themeButtons.forEach((button) => {
            button.setAttribute('aria-pressed', String(dark));
            const label = button.querySelector('span');
            if (label) label.textContent = dark ? 'Modo claro' : 'Modo escuro';
        });
    }

    themeButtons.forEach((button) => button.addEventListener('click', () => {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        const theme = dark ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-theme-preference', theme);
        try { localStorage.setItem('theme', theme); } catch (error) { /* O tema ainda funciona nesta página. */ }
        updateThemeButtons();
    }));
    updateThemeButtons();

    const stepCopy = [
        ['Comece por aqui', 'Que perfil você quer criar?', 'Escolha como deseja aparecer no Conectado em Sergipe.', 'Continuar para minha cobertura'],
        ['Onde você atende', 'Mostre onde seu trabalho chega.', 'Escolha sua cidade principal e outras regiões atendidas.', 'Continuar para minha vitrine'],
        ['Último passo', 'Dê um rosto ao seu trabalho.', 'Conte o que você faz e publique seu perfil profissional.', 'Criar minha conta e perfil'],
    ];

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);
    const fieldValue = (selector) => document.querySelector(selector)?.value?.trim() || '';
    const selectedKind = () => form.querySelector('input[name="profile_kind"]:checked')?.value || '';
    const selectedServices = () => [...form.querySelectorAll('input[name="services[]"]:checked')].map((input) => input.value);

    function previewMarkup(compact = false) {
        const name = fieldValue('#name') || 'Seu nome profissional';
        const category = fieldValue('#category') || 'Sua categoria';
        const city = fieldValue('#main_city') || 'Sua cidade';
        const services = selectedServices();
        const initials = name === 'Seu nome profissional'
            ? 'CS'
            : name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
        const image = state.photoUrl
            ? `<img src="${escapeHtml(state.photoUrl)}" alt="Prévia da foto">`
            : `<span>${escapeHtml(initials)}</span>`;
        const chips = compact || services.length === 0
            ? ''
            : `<div class="quick-profile-live-services">${services.slice(0, 3).map((service) => `<span>${escapeHtml(service)}</span>`).join('')}</div>`;

        return `<article class="quick-profile-live-card">
            <div class="quick-profile-live-head"><span>Prévia ao vivo</span><span>Rascunho</span></div>
            <div class="quick-profile-live-identity">
                <div class="quick-profile-live-photo">${image}</div>
                <div class="quick-profile-live-copy"><strong>${escapeHtml(name)}</strong><span>${escapeHtml(category)}</span><span><i class="fa-solid fa-location-dot"></i> ${escapeHtml(city)}</span></div>
            </div>${chips}
        </article>`;
    }

    function renderPreview() {
        const desktop = document.querySelector('#quick-desktop-preview');
        const mobile = document.querySelector('#quick-mobile-preview');
        if (desktop) desktop.innerHTML = previewMarkup();
        if (mobile) mobile.innerHTML = previewMarkup(true);
        const summary = document.querySelector('#quick-category-summary');
        if (summary) summary.textContent = fieldValue('#category') || 'Sua categoria';
    }

    function updateCategoryOptions(kind, preferredCategory = '') {
        const available = Array.isArray(categories[kind]) ? categories[kind] : [];
        categorySelect.innerHTML = '<option value="">Selecione uma categoria</option>';
        available.forEach((category) => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            option.selected = category === preferredCategory;
            categorySelect.append(option);
        });
    }

    function updateServiceOptions(kind, preferredServices = []) {
        const available = Array.isArray(categories[kind]) ? categories[kind] : [];
        serviceGrid.innerHTML = '';
        available.forEach((service) => {
            const label = document.createElement('label');
            label.className = 'quick-profile-service-card';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'services[]';
            input.value = service;
            input.checked = preferredServices.includes(service);
            const text = document.createElement('span');
            text.textContent = service;
            label.append(input, text);
            serviceGrid.append(label);
        });
    }

    function updateProfileKind(kind, preserveOld = false) {
        updateCategoryOptions(kind, preserveOld ? oldCategory : '');
        updateServiceOptions(kind, preserveOld ? oldServices : []);
        const liberal = kind === 'liberal_professional';
        liberalFields.hidden = !liberal;
        liberalFields.querySelectorAll('input').forEach((input) => { input.required = liberal; });
        renderPreview();
    }

    function goTo(step) {
        state.step = Math.max(0, Math.min(2, step));
        sections.forEach((section, index) => { section.hidden = index !== state.step; });
        tabs.forEach((tab) => {
            const target = Number(tab.dataset.goStep);
            tab.classList.toggle('is-active', target === state.step);
            tab.classList.toggle('is-complete', target < state.step);
        });
        document.querySelector('#quick-step-kicker').textContent = stepCopy[state.step][0];
        document.querySelector('#quick-step-title').textContent = stepCopy[state.step][1];
        document.querySelector('#quick-step-description').textContent = stepCopy[state.step][2];
        nextButton.innerHTML = `${escapeHtml(stepCopy[state.step][3])} <i class="fa-solid ${state.step === 2 ? 'fa-circle-check' : 'fa-arrow-right'}"></i>`;
        backButton.hidden = state.step === 0;
        document.querySelector('#quick-side-step').textContent = String(state.step + 1).padStart(2, '0');
        renderPreview();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function invalidate(element) {
        element?.classList.add('is-invalid');
        element?.focus?.();
        element?.reportValidity?.();
        return false;
    }

    function validateStep() {
        form.querySelectorAll('.is-invalid').forEach((element) => element.classList.remove('is-invalid'));

        if (state.step === 0) {
            if (!selectedKind()) {
                document.querySelector('.quick-profile-kind-grid')?.classList.add('is-invalid');
                form.querySelector('input[name="profile_kind"]')?.focus();
                return false;
            }
            const required = [...sections[0].querySelectorAll('input[required], select[required]')].filter((input) => !input.closest('[hidden]'));
            const invalid = required.find((input) => !input.checkValidity());
            if (invalid) return invalidate(invalid);
        }

        if (state.step === 1) {
            const city = document.querySelector('#main_city');
            if (!city.checkValidity()) return invalidate(city);
            const checkedCities = form.querySelectorAll('input[name="cities[]"]:checked');
            if (checkedCities.length > 5) return invalidate(document.querySelector('#city_search'));
        }

        if (state.step === 2) {
            if (selectedServices().length === 0) {
                serviceGrid.classList.add('is-invalid');
                serviceGrid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            if (!description.checkValidity()) return invalidate(description);
            const terms = form.querySelector('input[name="terms"]');
            if (!terms.checked) {
                terms.setCustomValidity('Aceite os Termos de Uso para continuar.');
                terms.reportValidity();
                terms.setCustomValidity('');
                return false;
            }
        }

        return true;
    }

    form.addEventListener('submit', (event) => {
        if (!validateStep()) {
            event.preventDefault();
            return;
        }
        if (state.step < 2) {
            event.preventDefault();
            goTo(state.step + 1);
            return;
        }
        nextButton.disabled = true;
        nextButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Criando sua conta e perfil…';
    });

    backButton.addEventListener('click', () => goTo(state.step - 1));
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        const target = Number(tab.dataset.goStep);
        if (target <= state.step) goTo(target);
    }));
    form.querySelectorAll('input[name="profile_kind"]').forEach((input) => input.addEventListener('change', () => updateProfileKind(input.value)));
    form.addEventListener('input', renderPreview);
    form.addEventListener('change', renderPreview);
    categorySelect.addEventListener('change', () => {
        const matchingService = [...serviceGrid.querySelectorAll('input')].find((input) => input.value === categorySelect.value);
        if (matchingService && selectedServices().length === 0) matchingService.checked = true;
        renderPreview();
    });
    serviceGrid.addEventListener('change', (event) => {
        if (event.target.matches('input[type="checkbox"]') && selectedServices().length > 5) {
            event.target.checked = false;
            event.target.setCustomValidity('Escolha no máximo cinco opções.');
            event.target.reportValidity();
            event.target.setCustomValidity('');
        }
        renderPreview();
    });
    citySearch?.addEventListener('input', () => {
        const query = citySearch.value.toLocaleLowerCase('pt-BR');
        document.querySelectorAll('.quick-profile-city-grid label').forEach((label) => {
            label.hidden = !label.textContent.toLocaleLowerCase('pt-BR').includes(query);
        });
    });
    description?.addEventListener('input', () => { document.querySelector('#quick-description-count').textContent = description.value.length; });
    document.querySelector('#phone')?.addEventListener('input', (event) => {
        const digits = event.target.value.replace(/\D/g, '').slice(0, 11);
        event.target.value = digits.length > 10
            ? digits.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3')
            : digits.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
    });

    const initialKind = selectedKind() || root.dataset.initialKind;
    if (initialKind && categories[initialKind]) {
        const radio = form.querySelector(`input[name="profile_kind"][value="${CSS.escape(initialKind)}"]`);
        if (radio) radio.checked = true;
        updateProfileKind(initialKind, true);
    } else {
        updateCategoryOptions('');
    }
    description?.dispatchEvent(new Event('input'));
    const initialStep = Number.parseInt(root.dataset.initialStep || '0', 10);
    goTo(Number.isNaN(initialStep) ? 0 : initialStep);

    // Editor quadrado para a primeira foto, adaptado da página fornecida pelo usuário.
    const editor = document.querySelector('#quick-crop-editor');
    const canvas = document.querySelector('#quick-crop-canvas');
    const zoom = document.querySelector('#quick-crop-zoom');
    const thumbs = document.querySelector('#quick-photo-thumbs');
    if (!photoInput || !editor || !canvas || !canvas.getContext) return;

    const context = canvas.getContext('2d');
    let image = new Image();
    let sourceFile = null;
    let scale = 1;
    let offsetX = 0;
    let offsetY = 0;
    let dragging = false;
    let startX = 0;
    let startY = 0;

    function drawCrop() {
        if (!image.naturalWidth) return;
        const base = Math.max(canvas.width / image.naturalWidth, canvas.height / image.naturalHeight);
        const width = image.naturalWidth * base * scale;
        const height = image.naturalHeight * base * scale;
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, (canvas.width - width) / 2 + offsetX, (canvas.height - height) / 2 + offsetY, width, height);
    }

    function openCrop(file) {
        sourceFile = file;
        image = new Image();
        image.onload = () => {
            scale = 1; offsetX = 0; offsetY = 0; zoom.value = '1';
            drawCrop(); editor.hidden = false;
        };
        image.src = URL.createObjectURL(file);
    }

    function renderThumbs() {
        thumbs.innerHTML = '';
        [...photoInput.files].slice(0, 5).forEach((file) => {
            const preview = document.createElement('img');
            preview.src = URL.createObjectURL(file);
            preview.alt = 'Foto selecionada';
            thumbs.append(preview);
        });
        const files = Math.min(photoInput.files.length, 5);
        photoLabel.textContent = files ? `${files} foto${files > 1 ? 's' : ''} selecionada${files > 1 ? 's' : ''}` : 'Escolher fotos';
        if (photoInput.files[0]) state.photoUrl = URL.createObjectURL(photoInput.files[0]);
        renderPreview();
    }

    photoInput.addEventListener('change', () => {
        renderThumbs();
        if (photoInput.files[0]) openCrop(photoInput.files[0]);
    });
    zoom.addEventListener('input', () => { scale = Number(zoom.value); drawCrop(); });
    document.querySelector('#quick-close-crop').addEventListener('click', () => { editor.hidden = true; });
    document.querySelector('#quick-cancel-crop').addEventListener('click', () => { editor.hidden = true; });
    canvas.addEventListener('pointerdown', (event) => {
        dragging = true; startX = event.clientX - offsetX; startY = event.clientY - offsetY; canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!dragging) return;
        offsetX = event.clientX - startX; offsetY = event.clientY - startY; drawCrop();
    });
    canvas.addEventListener('pointerup', () => { dragging = false; });
    canvas.addEventListener('pointercancel', () => { dragging = false; });
    document.querySelector('#quick-confirm-crop').addEventListener('click', () => {
        canvas.toBlob((blob) => {
            if (!blob || !sourceFile || typeof DataTransfer === 'undefined') {
                editor.hidden = true;
                return;
            }
            const cropped = new File([blob], `perfil-${Date.now()}.jpg`, { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(cropped);
            [...photoInput.files].slice(1, 5).forEach((file) => transfer.items.add(file));
            photoInput.files = transfer.files;
            editor.hidden = true;
            renderThumbs();
        }, 'image/jpeg', .9);
    });
});
