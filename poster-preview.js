const posterInput = document.querySelector('#poster');
const posterPreview = document.querySelector('#poster-preview');
if (posterInput && posterPreview) {
  const original = posterPreview.getAttribute('src');
  let previewUrl;
  posterInput.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    const file = posterInput.files[0];
    posterInput.setCustomValidity('');
    if (file && (file.size > 2 * 1024 * 1024 || !['image/jpeg','image/png','image/webp'].includes(file.type))) {
      posterInput.setCustomValidity('Pilih JPG, PNG, atau WebP maksimal 2 MB.');
      posterInput.reportValidity();
      posterPreview.hidden = !original;
      if (original) posterPreview.src = original;
      return;
    }
    previewUrl = file ? URL.createObjectURL(file) : null;
    posterPreview.hidden = !previewUrl && !original;
    if (previewUrl || original) posterPreview.src = previewUrl || original;
  });
}
