document.addEventListener('DOMContentLoaded', function() {
    // Drag & Drop für Mediathek
    const mediaRows = document.querySelectorAll('.ea-index tbody tr');
    const folderRows = [];
    
    // Identifiziere Ordner-Zeilen (enthalten 📁 Symbol)
    mediaRows.forEach(row => {
        const typeCell = row.querySelector('td:nth-child(3)'); // Typ-Spalte
        if (typeCell && typeCell.textContent.includes('📁 Ordner')) {
            folderRows.push(row);
            row.classList.add('media-folder');
            row.style.cursor = 'pointer';
            row.style.backgroundColor = '#f8f9fa';
        }
    });
    
    // Drag & Drop für Dateien
    mediaRows.forEach(row => {
        const typeCell = row.querySelector('td:nth-child(3)');
        if (typeCell && typeCell.textContent.includes('📄 Datei')) {
            row.draggable = true;
            row.classList.add('media-file');
            
            row.addEventListener('dragstart', function(e) {
                e.dataTransfer.effectAllowed = 'move';
                const entityId = row.querySelector('input[type="checkbox"]')?.value;
                e.dataTransfer.setData('text/plain', entityId);
                row.style.opacity = '0.5';
            });
            
            row.addEventListener('dragend', function(e) {
                row.style.opacity = '1';
            });
        }
    });
    
    // Drop-Zonen für Ordner
    folderRows.forEach(folderRow => {
        folderRow.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            folderRow.style.backgroundColor = '#d1ecf1';
            folderRow.style.borderLeft = '4px solid #0c5460';
        });
        
        folderRow.addEventListener('dragleave', function(e) {
            folderRow.style.backgroundColor = '#f8f9fa';
            folderRow.style.borderLeft = 'none';
        });
        
        folderRow.addEventListener('drop', function(e) {
            e.preventDefault();
            folderRow.style.backgroundColor = '#f8f9fa';
            folderRow.style.borderLeft = 'none';
            
            const fileId = e.dataTransfer.getData('text/plain');
            const folderId = folderRow.querySelector('input[type="checkbox"]')?.value;
            
            if (fileId && folderId) {
                // AJAX-Request zum Verschieben
                fetch(window.location.pathname + '?crudAction=moveFileToFolder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        fileId: fileId,
                        folderId: folderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Erfolgsmeldung anzeigen
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                            <strong>Erfolg!</strong> Datei wurde verschoben.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.querySelector('.content-wrapper').prepend(alert);
                        
                        // Seite nach 1 Sekunde neu laden
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Fehler beim Verschieben der Datei.');
                });
            }
        });
    });
    
    // Doppelklick auf Ordner zum Öffnen
    folderRows.forEach(folderRow => {
        folderRow.addEventListener('dblclick', function() {
            const folderId = folderRow.querySelector('input[type="checkbox"]')?.value;
            if (folderId) {
                window.location.href = window.location.pathname + '?filters[parent]=' + folderId;
            }
        });
    });
});
