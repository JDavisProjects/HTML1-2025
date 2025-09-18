// PHPA1JDavis | BabelBookBrowser

// DOM elements
const searchBtn = document.querySelector('.btn-primary');
const titleInput = document.getElementById('title-search');
const genreSelect = document.getElementById('genre');
const authorInput = document.getElementById('author');
const pageMinInput = document.getElementById('pageMin');
const pageMaxInput = document.getElementById('pageMax');
const resultsSection = document.getElementById('results-section')
const resultsDiv = document.getElementById('results')

//Search function
function searchBooks() {
    //Search parameters
    const searchParams = {
        title: titleInput.value.trim(),
        genre: genreSelect.value,
        author: authorInput.value.trim(),
        pageMin: pageMinInput.value,
        PageMax: pageMaxInput.value
    };

    //check to see if minimum one search filter is being used
    if (!searchParams.title && !searchParams.genre && !searchParams.author) {
        alert('Please enter one search criteria')
        return;
    }

    //Loading message
    resultsDiv.innerHTML = '<div class="loading">Searching for books...</div>';
    resultsSection.style.display = 'block';

    // Create query string
    const queryString = new URLSearchParams(searchParams).toString();

    // Fetch from PHP file
    fetch(`PHPA1JDavis.php?${queryString}`)
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            resultsDiv.innerHTML = '<div class="error-message">Error searching for books. Please try again.</div>';
            console.error('Error:', error);
        });
}
//Display results
function displayResults(data) {
    resultsDiv.innerHTML = '';

    //check if results
    if (!data.items || data.items.length === 0) {
        resultsDiv.innerHTML = '<p>No books found. Try a different search.</p>';
        return;
    }
    // Display each book
    data.items.forEach(book => {
        const bookInfo = book.volumeInfo;

        // Create book card
        const bookCard = document.createElement('div');
        bookCard.className = 'book-card';

        //fetch book detail w/fallbacks
        const title = bookInfo.title || 'Unknown Title';
        const authors = bookInfo.author ? bookInfo.author.join(',') : 'Unknown Author';
        const thumbnail = bookInfo.imageLinks?.thumbnail || 'https://via.placeholder.com/128x192?text=No+Cover';
        const pageCount = bookInfo.pageCount || 'N/A';

        //Book card HTML
        bookCard.innerHTML = `
            <img src="${thumbnail}" alt="${title}" class="book-cover">
            <div class="book-title">${title}</div>
            <div class="book-author">${authors}</div>
            <div class="book-pages">${pageCount} pages</div>
        `;
        //Click event for displaying book info
        if (bookInfo.infoLink) {
            bookCard.style.cursor = 'pointer';
            bookCard.onclick = () => window.open(bookInfo.infoLink);
        }

        resultsDiv.appendChild(bookCard);
    })
}

//event listeners
searchBtn.addEventListener('click', (e) => {
    e.preventDefault();
    searchBooks();
});

// Allow Enter key to search
titleInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchBooks();
    }
});




