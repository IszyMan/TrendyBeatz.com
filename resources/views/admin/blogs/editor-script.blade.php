<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bodyInput = document.getElementById('blog-body-input');
    const form = document.getElementById('blog-form');
    const imageInput = document.getElementById('blog-photo-input');
    const imagePreview = document.getElementById('blog-photo-preview');

    const quill = new Quill('#blog-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote'],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    if (bodyInput.value.trim()) {
        quill.clipboard.dangerouslyPasteHTML(bodyInput.value);
    }

    form.addEventListener('submit', () => {
        bodyInput.value = quill.getSemanticHTML();
    });

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];

        if (!file) {
            imagePreview.removeAttribute('src');
            imagePreview.style.display = 'none';
            return;
        }

        imagePreview.src = URL.createObjectURL(file);
        imagePreview.style.display = 'block';

        imagePreview.onload = () => {
            URL.revokeObjectURL(imagePreview.src);
        };
    });
});
</script>