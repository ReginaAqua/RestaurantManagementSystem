document.addEventListener("DOMContentLoaded", function() {
    // Elements from the order form
    const orderSearch = document.getElementById('orderSearch');
    const suggestionsDiv = document.getElementById('suggestions');
    const orderList = document.getElementById('orderList');
    const hiddenOrderInput = document.getElementById('order');

    // List of available foods
    const availableFoods = ["Simple Pizza", "Special Pizza", "Pasta", "Carbonara", "Salad", "Greek Salad", "Trash"];

    // Function to update the hidden "order" input based on current order list items
    function updateHiddenOrder() {
        const items = Array.from(orderList.querySelectorAll('li')).map(li => li.firstChild.textContent.trim());
        hiddenOrderInput.value = items.join(', ');
    }

    // Function to display suggestions as the user types
    orderSearch.addEventListener('input', function() {
        const query = orderSearch.value.toLowerCase();
        suggestionsDiv.innerHTML = '';
        if (query === '') {
            suggestionsDiv.style.display = 'none';
            return;
        }
        const filtered = availableFoods.filter(food => food.toLowerCase().includes(query));
        if (filtered.length === 0) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        suggestionsDiv.style.display = 'block';
        const ul = document.createElement('ul');
        filtered.forEach(food => {
            const li = document.createElement('li');
            li.textContent = food;
            li.addEventListener('click', function() {
                // Check for duplicates
                let exists = false;
                orderList.querySelectorAll('li').forEach(existing => {
                    if(existing.firstChild.textContent.trim() === food) {
                        exists = true;
                    }
                });
                if (!exists) {
                    // Create a new list item with a delete option
                    const newItem = document.createElement('li');
                    newItem.innerHTML = food + ' <span style="color:red; cursor:pointer;">x</span>';
                    newItem.querySelector('span').addEventListener('click', function() {
                        orderList.removeChild(newItem);
                        updateHiddenOrder();
                    });
                    orderList.appendChild(newItem);
                    updateHiddenOrder();
                }
                // Clear the search input and hide suggestions
                orderSearch.value = '';
                suggestionsDiv.innerHTML = '';
                suggestionsDiv.style.display = 'none';
            });
            ul.appendChild(li);
        });
        suggestionsDiv.appendChild(ul);
    });

    // Hide suggestions if clicking outside of the search input
    document.addEventListener('click', function(event) {
        if (event.target !== orderSearch) {
            suggestionsDiv.style.display = 'none';
        }
    });
});
