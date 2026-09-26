// Presentation-only interactions. No API calls, credentials, storage, or mutations.
const dialog = document.querySelector('#feedback-dialog');
const toast = document.querySelector('#toast');
let toastTimer;
function showToast(message) {
    if (!toast) return;
    toast.textContent = message; toast.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.hidden = true; }, 6000);
}
function showDialog(title, message, confirmation = false) {
    if (!dialog) return;
    dialog.querySelector('#feedback-title').textContent = title;
    dialog.querySelector('#feedback-message').textContent = message;
    dialog.querySelector('#feedback-confirm').hidden = !confirmation;
    dialog.showModal();
}
document.addEventListener('click', (event) => {
    const menu = event.target.closest('[data-menu]');
    if (menu) { const nav = document.getElementById(menu.dataset.menu); if (nav) menu.setAttribute('aria-expanded', String(nav.classList.toggle('is-open'))); }
    const feedback = event.target.closest('[data-feedback-message]');
    if (feedback) showDialog(feedback.dataset.feedbackTitle, feedback.dataset.feedbackMessage);
    const confirm = event.target.closest('[data-confirm-message]');
    if (confirm) showDialog(confirm.dataset.confirmTitle, confirm.dataset.confirmMessage, true);
    if (event.target.closest('[data-close]')) dialog?.close();
    if (event.target.closest('#feedback-confirm')) { dialog?.close(); showToast('Konfirmasi dicoba. Tidak ada data yang diubah pada pratinjau.'); }
    const add = event.target.closest('[data-add-row]');
    if (add) {
        const collection = document.querySelector(`[data-collection="${add.dataset.addRow}"]`);
        const template = document.getElementById(`row-${add.dataset.addRow}`);
        if (!collection || !template) return;
        const index = Number(collection.dataset.nextIndex || collection.children.length);
        collection.dataset.nextIndex = String(index + 1);
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach((el) => { el.name = el.name.replace('INDEX', String(index)); });
        collection.append(fragment); collection.lastElementChild.querySelector('input')?.focus();
    }
    const remove = event.target.closest('[data-remove-row]');
    if (remove) {
        const collection = remove.closest('[data-collection]');
        collection.dataset.nextIndex ||= String(collection.children.length);
        const nextFocus = remove.closest('.repeat-row').nextElementSibling?.querySelector('input') || document.querySelector(`[data-add-row="${collection.dataset.collection}"]`);
        remove.closest('.repeat-row').remove(); nextFocus?.focus();
        showToast('Baris dihapus dari formulir contoh. Data tersimpan belum berubah.');
    }
    const edit = event.target.closest('[data-review-edit]');
    if (edit) {
        const form = document.querySelector('#review-form');
        form.elements.reviewer_name.value = edit.dataset.name;
        form.elements.content.value = edit.dataset.content;
        form.elements.rating_value.value = edit.dataset.rating;
        form.elements.is_visible.value = edit.dataset.visible;
        form.elements.provenance.value = 'Referensi Shopee — data contoh';
        document.querySelector('#review-form-title').textContent = 'Edit referensi contoh';
        form.scrollIntoView({ block: 'start' }); form.elements.reviewer_name.focus({ preventScroll: true });
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-menu][aria-expanded="true"]').forEach((button) => { document.getElementById(button.dataset.menu)?.classList.remove('is-open'); button.setAttribute('aria-expanded', 'false'); button.focus(); });
});
document.querySelectorAll('[data-media]').forEach((image) => {
    const fallback = () => { image.hidden = true; const note = image.parentElement.querySelector('.media-fallback'); if (note) note.hidden = false; };
    image.addEventListener('error', fallback); if (image.complete && image.naturalWidth === 0) fallback();
});
document.querySelectorAll('[data-slug-source]').forEach((input) => {
    const target = input.form.querySelector('[data-slug-target]'); let manual = Boolean(target.value);
    target.addEventListener('input', () => { manual = true; });
    input.addEventListener('input', () => { if (!manual) target.value = input.value.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); });
});
document.querySelectorAll('[data-preview-form]').forEach((form, formIndex) => {
    // Without JavaScript POST forms fail closed (405), never leak inputs in a URL.
    form.noValidate = true;
    const summary = form.querySelector('.form-errors'); const success = form.querySelector('.form-success');
    let nextFieldId = 0;
    form.addEventListener('submit', (event) => {
        event.preventDefault(); const errors = [];
        const fields = [...form.querySelectorAll('input,select,textarea')];
        form.querySelectorAll('[data-inline-error]').forEach((el) => el.remove());
        fields.forEach((field) => {
            field.removeAttribute('aria-invalid'); field.setCustomValidity('');
            if (field.dataset.originalDescription !== undefined) {
                if (field.dataset.originalDescription) field.setAttribute('aria-describedby', field.dataset.originalDescription);
                else field.removeAttribute('aria-describedby');
            }
            if (!field.id) field.id = `preview-field-${formIndex}-${nextFieldId++}`;
        });
        const invalid = (field, message) => {
            if (!field || field.getAttribute('aria-invalid') === 'true') return;
            field.setAttribute('aria-invalid', 'true'); errors.push({ field, message });
            field.dataset.originalDescription ??= field.getAttribute('aria-describedby') || '';
            const note = document.createElement('span'); note.id = `${field.id}-error`;
            note.className = 'inline-error'; note.dataset.inlineError = ''; note.textContent = message;
            // A checkbox's explanation belongs after its surrounding label.
            if (field.type === 'checkbox' || field.type === 'radio') field.closest('label').after(note);
            else field.after(note);
            field.setAttribute('aria-describedby', [field.dataset.originalDescription, note.id].filter(Boolean).join(' '));
        };
        fields.forEach((field) => {
            if (field.disabled || field.type === 'hidden') return;
            const name = field.closest('label')?.childNodes[0]?.textContent.trim() || field.name;
            if (!field.checkValidity()) invalid(field, `${name}: ${field.validity.valueMissing ? 'wajib diisi.' : 'periksa format atau rentang nilainya.'}`);
            if (field.matches('[data-https]') && field.value.trim()) {
                try { const url = new URL(field.value); if (url.protocol !== 'https:' || url.username || url.password || field.value.length > 2048) invalid(field, `${name}: gunakan URL HTTPS tanpa username/password, maksimal 2048 karakter.`); }
                catch { invalid(field, `${name}: URL tidak valid.`); }
            }
        });
        if (form.hasAttribute('data-product-form')) {
            const active = form.querySelector('[name="is_active"]:checked')?.value === '1'; const category = form.elements.category_id;
            if (active && category.selectedOptions[0]?.dataset.active !== '1') invalid(category, 'Produk aktif harus memiliki kategori aktif.');
            if (active && !form.elements.electronics_scope_confirmed.checked) invalid(form.elements.electronics_scope_confirmed, 'Centang konfirmasi lingkup elektronik untuk menyimpan produk aktif.');
            if (form.elements.price_amount.value !== '' && !form.elements.price_currency.value) invalid(form.elements.price_currency, 'Pilih mata uang ketika harga diisi.');
            const specNames = new Set();
            form.querySelectorAll('[data-collection="specifications"] .repeat-row').forEach((row) => {
                const [name, value] = row.querySelectorAll('input');
                if (name.value.trim() && !value.value.trim()) invalid(value, 'Isi nilai untuk spesifikasi yang diberi nama.');
                if (!name.value.trim() && value.value.trim()) invalid(name, 'Isi nama spesifikasi.');
                const normalized = name.value.trim().toLowerCase(); if (normalized && specNames.has(normalized)) invalid(name, 'Nama spesifikasi tidak boleh berulang.'); specNames.add(normalized);
            });
        }
        if (form.hasAttribute('data-category-form') && form.elements.is_active.value === '0' && Number(form.dataset.activeProducts) > 0) invalid(form.elements.is_active, 'Kategori masih digunakan produk aktif. Tidak ada perubahan yang disimpan.');
        summary.hidden = errors.length === 0; success.hidden = true; const list = summary.querySelector('ul'); list.replaceChildren();
        if (errors.length) {
            for (const { field, message } of errors) { const item = document.createElement('li'); const link = document.createElement('a'); link.href = `#${field.id}`; link.textContent = message; link.addEventListener('click', (e) => { e.preventDefault(); field.focus(); }); item.append(link); list.append(item); }
            summary.focus();
        } else {
            success.textContent = form.dataset.successMessage || 'Pemeriksaan tampilan berhasil. Ini simulasi: belum ada data yang disimpan atau persetujuan yang diberikan. Penyimpanan dan validasi akhir memerlukan backend.'; success.hidden = false; success.focus();
        }
        if (form.elements.electronics_scope_confirmed) form.elements.electronics_scope_confirmed.checked = false;
    });
    form.addEventListener('reset', () => { summary.hidden = true; success.hidden = true; form.querySelectorAll('[aria-invalid]').forEach((field) => { field.removeAttribute('aria-invalid'); field.removeAttribute('aria-describedby'); }); form.querySelectorAll('[data-inline-error]').forEach((el) => el.remove()); if (form.id === 'review-form') document.querySelector('#review-form-title').textContent = 'Tambah referensi'; });
});
document.querySelectorAll('.table-scroll').forEach((region) => {
    region.setAttribute('tabindex', '0');
    region.setAttribute('role', 'region');
    region.setAttribute('aria-label', 'Tabel, geser untuk melihat seluruh kolom');
});
