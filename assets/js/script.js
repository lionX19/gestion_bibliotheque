// Confirmation avant suppression
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = searchInput.value.toUpperCase();
            let table = document.querySelector('table');
            let tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                let visible = false;
                let tds = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < tds.length; j++) {
                    let cell = tds[j];
                    if (cell) {
                        let txtValue = cell.textContent || cell.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            visible = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = visible ? '' : 'none';
            }
        });
    }
    
    // Datepicker pour les dates
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // S'assurer que les dates sont au format YYYY-MM-DD
        input.addEventListener('change', function() {
            if (!input.value) {
                return;
            }
            
            const date = new Date(input.value);
            if (isNaN(date.getTime())) {
                input.value = '';
                alert('Format de date invalide. Utilisez le format YYYY-MM-DD.');
            }
        });
    });
    
    // Validation du nombre d'exemplaires
    const exemplaireInput = document.getElementById('exemplaires');
    if (exemplaireInput) {
        exemplaireInput.addEventListener('input', function() {
            if (parseInt(this.value) < 1) {
                this.value = 1;
            }
        });
    }
});