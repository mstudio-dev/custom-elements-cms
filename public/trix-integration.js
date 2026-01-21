// Trix Editor Integration für EasyAdmin
document.addEventListener('DOMContentLoaded', function() {
    // Konvertiere alle Textareas mit data-richtext="true" zu Trix-Editoren
    function initializeTrixEditors() {
        document.querySelectorAll('textarea[data-richtext="true"]').forEach(function(textarea) {
            // Prüfe ob bereits konvertiert
            if (textarea.dataset.trixInitialized) return;
            textarea.dataset.trixInitialized = 'true';
            
            // Erstelle Hidden Input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.id = textarea.id + '_trix';
            input.name = textarea.name;
            input.value = textarea.value;
            
            // Erstelle Trix Editor
            const editor = document.createElement('trix-editor');
            editor.setAttribute('input', input.id);
            editor.classList.add('form-control');
            
            // Ersetze Textarea
            textarea.parentNode.insertBefore(input, textarea);
            textarea.parentNode.insertBefore(editor, textarea);
            textarea.style.display = 'none';
            
            // Sync bei Änderungen
            editor.addEventListener('trix-change', function() {
                textarea.value = input.value;
            });
        });
    }
    
    // Initialisiere sofort
    initializeTrixEditors();
    
    // Beobachte DOM-Änderungen für dynamisch geladene Formulare
    const observer = new MutationObserver(function(mutations) {
        initializeTrixEditors();
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
