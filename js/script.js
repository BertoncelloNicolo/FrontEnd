function loadProducts() {
    fetch('http://127.0.0.1:20000/products')
        .then(response => response.json())
        .then(data => {
            // Ottieni il riferimento all'elemento tbody della tabella
            var tbody = document.getElementById('productTableBody');
            // Pulisci il contenuto della tabella
            tbody.innerHTML = '';

            // Itera su ogni prodotto e aggiungi una riga nella tabella
            data.data.forEach(product => {
                var row = `<tr id = "row_${product.id}">
                                <td id = "cella-productID-${product.id}">${product.id}</td>
                                <td id = "cella-nome-${product.id}">${product.attributes.nome}</td>
                                <td id = "cella-marca-${product.id}">${product.attributes.marca}</td>
                                <td id = "cella-prezzo-${product.id}">${product.attributes.prezzo}</td>
                                <td>
                                    <a href="#" onclick="showProduct(${product.id})">Show</a> |
                                    <a href="#" onclick="editProduct(${product.id})">Edit</a> |
                                    <a href="#" onclick="deleteProduct(${product.id})">Delete</a>
                                </td>
                            </tr>`;
                tbody.innerHTML += row;
            });
        })
        .catch(error => console.error('Errore durante il recupero dei prodotti:', error));
}

function showProduct(productId) {
    // Recupera il prodotto dall'API
    fetch(`http://127.0.0.1:20000/products/${productId}`)
        .then(response => response.json())
        .then(product => {
            // Popola il modal con i dettagli del prodotto
            document.getElementById('modalTitle').innerText = 'Dettagli Prodotto';
            document.getElementById('nome').value = product.data.attributes.nome;
            document.getElementById('marca').value = product.data.attributes.marca;
            document.getElementById('prezzo').value = product.data.attributes.prezzo;
            // Mostra il modal
            var modal = new bootstrap.Modal(document.getElementById('exampleModal'));
            modal.show();
        })
        .catch(error => console.error('Errore durante il recupero del prodotto:', error));
}

// Funzione per eliminare un prodotto
function deleteProduct(productId) {
    if (confirm("Sei sicuro di voler eliminare il prodotto?")) {
        // Esegui la richiesta di eliminazione del prodotto
        fetch(`http://127.0.0.1:20000/products/${productId}`, {
            method: 'DELETE'
        })
            .then(response => {
                if (response.status === 204) {
                    // Prodotto eliminato con successo dal server
                    const productRow = document.getElementById(`row_${productId}`).remove();
                    alert('Prodotto eliminato con successo');
                    // Rimuovi l'elemento corrispondente dalla pagina senza ricaricare

                } else if (response.status === 404) {
                    // Prodotto non trovato
                    alert('Prodotto non trovato');
                } else if (response.status === 500) {
                    // Errore durante l'eliminazione del prodotto
                    response.json().then(data => {
                        alert(data.error);
                    });
                } else {
                    throw new Error('Errore durante l\'eliminazione del prodotto');
                }
            })
            .catch(error => console.error('Errore:', error));
    }
}

function openAddProductModal() {
    var modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();

    var addProduct = document.getElementById('addProductForm');

    function submit(event) {
        event.preventDefault();

        var nomeProdotto = document.getElementById('nomeProdotto').value;
        var marcaProdotto = document.getElementById('marcaProdotto').value;
        var prezzoProdotto = document.getElementById('prezzoProdotto').value;

        fetch('http://127.0.0.1:20000/products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                data: {
                    attributes: {
                        nome: nomeProdotto,
                        marca: marcaProdotto,
                        prezzo: prezzoProdotto
                    }
                }
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore durante l\'aggiunta del prodotto');
                }
                return response.json();
            })
            .then(data => {
                modal.hide();
                addProductRow(data);
                addProduct.reset();
            })
            .catch(error => console.error('Errore:', error));
    }

    addProduct.addEventListener('submit', submit);

    // Rimuovi l'evento submit dopo che è stato gestito una volta
    addProduct.addEventListener('submit', function removeSubmitListener() {
        addProduct.removeEventListener('submit', submit);
    });
}

// Funzione per creare un nuovo elemento HTML del praodotto
function addProductRow(product) {

    var productTableBody = document.getElementById('productTableBody');

    var newRowHtml = `
        <tr id = "row_${product.data.id}">
            <td id="cella-productID-${product.data.id}">${product.data.id}</td>
            <td id="cella-nome-${product.data.id}">${product.data.attributes.nome}</td>
            <td id="cella-marca-${product.data.id}">${product.data.attributes.marca}</td>
            <td id="cella-prezzo-${product.data.id}">${product.data.attributes.prezzo}</td>
            <td>
                <a href="#" onclick="showProduct(${product.data.id})">Show</a> |
                <a href="#" onclick="editProduct(${product.data.id})">Edit</a> |
                <a href="#" onclick="deleteProduct(${product.data.id})">Delete</a>
            </td>
        </tr>
    `;

    // Aggiungi la nuova riga al corpo della tabella
    productTableBody.innerHTML += newRowHtml;
}

function editProduct(productId) {
    // Seleziona il modal di modifica del prodotto
    var editProductModal = new bootstrap.Modal(document.getElementById('editProductModal'));

    function saveChanges() {
        var productName = document.getElementById('productNameInput').value;
        var productBrand = document.getElementById('productBrandInput').value;
        var productPrice = document.getElementById('productPriceInput').value;

        fetch(`http://127.0.0.1:20000/products/${productId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                data: {
                    attributes: {
                        nome: productName,
                        marca: productBrand,
                        prezzo: productPrice
                    }
                }
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore durante il salvataggio delle modifiche');
                }
                return response.json();
            })
            .then(updatedProduct => {
                editProductModal.hide();

                document.getElementById(`cella-nome-${updatedProduct.data.id}`).innerText = updatedProduct.data.attributes.nome;
                document.getElementById(`cella-marca-${updatedProduct.data.id}`).innerText = updatedProduct.data.attributes.marca;
                document.getElementById(`cella-prezzo-${updatedProduct.data.id}`).innerText = updatedProduct.data.attributes.prezzo;
            })
            .catch(error => console.error('Errore:', error));

        document.getElementById('modifiche').removeEventListener('click', saveChanges);
    }


    // Aggiungi il listener per il click al pulsante "Salva"
    document.getElementById('modifiche').addEventListener('click', saveChanges);

    fetch(`http://127.0.0.1:20000/products/${productId}`)
        .then(response => response.json())
        .then(product => {
            // Popola il form di modifica del prodotto con i dettagli recuperati
            document.getElementById('productNameInput').value = product.data.attributes.nome;
            document.getElementById('productBrandInput').value = product.data.attributes.marca;
            document.getElementById('productPriceInput').value = product.data.attributes.prezzo;

            editProductModal.show();
        })
        .catch(error => console.error('Errore durante il recupero del prodotto:', error));
}

// Chiamata alla funzione per caricare i prodotti quando la pagina è pronta
document.addEventListener('DOMContentLoaded', function () {
    loadProducts();
});
