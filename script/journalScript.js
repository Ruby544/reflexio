// Scroll header behavior
let lastScroll = 0;
const header = document.getElementById('main-header');

// Event listener to detect page scroll
window.addEventListener('scroll', () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll <= 0) {
    // When the user is at the top of the page, show the header
    header.classList.remove('transform', '-translate-y-full');
    return;
  }
  
  if (currentScroll > lastScroll && !header.classList.contains('-translate-y-full')) {
    // If scrolling down, hide the header
    header.classList.add('transform', '-translate-y-full');
  }
  
  lastScroll = currentScroll; // Update the last scroll position
});

// Current date tracking - CALENDAR STATE
let currentDate = new Date();
let currentYear = currentDate.getFullYear();
let currentMonth = currentDate.getMonth();
let selectedDate = null;
let journalEntries = {};  // Stores all journal entries indexed by date

// Initialize calendar
async function initCalendar() {
    await fetchEntries();       // Get entries from backend
    updateCalendar();           // Build calendar grid
    setupEventListeners();      // Attach UI event handlers
}

// Fetch entries from database
async function fetchEntries() {
    try {
        const response = await fetch(`../../php/journals/get_entries.php?year=${currentYear}&month=${currentMonth + 1}`);
        const data = await response.json();
        
        if (data.status === "success") {
            // Reset and repopulate journalEntries
            journalEntries = {};
            for (const [date, entries] of Object.entries(data.entries)) {
                journalEntries[date] = entries.map(entry => ({
                    journal_id: entry.journal_id,
                    title: entry.title,
                    content: entry.content
                }));
            }
        } else {
            console.error("Error fetching entries:", data.message);
            journalEntries = {};
        }
    } catch (error) {
        console.error("Fetch error:", error);
        journalEntries = {};
    }
}

// Update calendar UI
function updateCalendar() {
    const monthNames = ["January", "February", "March", "April", "May", "June",
                       "July", "August", "September", "October", "November", "December"];
    
    // Display current month and year
    document.getElementById('currentMonthYear').textContent = 
        `${monthNames[currentMonth]} ${currentYear}`;
    
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    
    const calendarGrid = document.getElementById('calendarGrid');
    calendarGrid.innerHTML = '';
    
    // Add empty placeholders for alignment
    for (let i = 0; i < firstDay; i++) {
        calendarGrid.appendChild(createEmptyDay());
    }
    
    // Add actual day cells
    for (let day = 1; day <= daysInMonth; day++) {
        calendarGrid.appendChild(createDayCell(day));
    }
}

// Create empty day cell
function createEmptyDay() {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day bg-gray-50';
    return dayDiv;
}

// Create a day cell with journal entries
function createDayCell(day) {
    const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    const entries = journalEntries[dateStr] || [];
    
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day';
    
    // Calculate dynamic height based on number of entries
    const baseHeight = 120; // Base height in pixels
    const entryHeight = 28; // Height per entry in pixels
    const calculatedHeight = baseHeight + (entries.length * entryHeight);
    dayDiv.style.minHeight = `${calculatedHeight}px`;
    
    // Day number display
    const dayNumber = document.createElement('div');
    dayNumber.className = 'text-sm font-semibold mb-1';
    dayNumber.textContent = day;
    dayDiv.appendChild(dayNumber);
    
    // Each Journal entry preview
    const entriesContainer = document.createElement('div');
    entries.forEach(entry => {
        const entryDiv = document.createElement('div');
        entryDiv.className = 'journal-entry';
        entryDiv.textContent = entry.title;
        entryDiv.onclick = () => openJournalPopup(dateStr, entry.journal_id);
        entriesContainer.appendChild(entryDiv);
    });
    dayDiv.appendChild(entriesContainer);
    
    // Button to add journal button
    const addButton = document.createElement('button');
    addButton.className = 'add-journal-btn text-blue-600 text-sm';
    addButton.innerHTML = '<i class="fas fa-plus"></i>';
    addButton.onclick = (e) => {
        e.stopPropagation();
        openNewEntryForm(dateStr);
    };
    dayDiv.appendChild(addButton);
    
    // Click handler to view all entries for the day
    dayDiv.onclick = () => openJournalPopup(dateStr);

    if (entries.length > 0) {
        dayDiv.classList.add('has-entries');
    }
    
    return dayDiv;
}

// JOURNAL POPUP
function openJournalPopup(dateStr, journalId = null) {
    selectedDate = dateStr;
    const date = new Date(dateStr);
    const formattedDate = date.toLocaleDateString('en-US', { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
    });
    
    document.getElementById('popupDate').textContent = formattedDate;
    const popupContent = document.getElementById('popupContent');
    popupContent.innerHTML = '';
    
    const entries = journalEntries[dateStr] || [];
    
    if (entries.length === 0) {
        popupContent.innerHTML = '<p class="text-gray-500">No entries for this day.</p>';
    } else if (journalId) {
        // Show full view for a specific entry
        const entry = entries.find(e => e.journal_id === journalId);
        if (entry) {
            const lines = entry.content.split('\n');
            const previewLines = lines.slice(0, 4).join('\n');
            const remainingLines = lines.length > 4 ? lines.slice(4).join('\n') : null;
            
            const entryHTML = `
  <div class="entry-container">
    <div class="popup-text-content">
      <h4 class="text-xl font-semibold mb-4">${entry.title}</h4>
      <div class="entry-content">
        ${entry.content}
      </div>
    </div>
    <div class="flex justify-end space-x-4 mt-6">
      <button id="editBtn" class="px-2 py-1 rounded" onclick="editEntry('${dateStr}', ${entry.journal_id})" >
        <i class="fa-solid fa-pen"></i>
      </button>
      <button id="deleteBtn"  class="px-2 py-1 rounded" onclick="deleteEntry('${dateStr}', ${entry.journal_id})" >
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
  </div>
`;
            popupContent.innerHTML = entryHTML;
        }
    } else {
        // Show all entries for the day with previews
        entries.forEach(entry => {
            const lines = entry.content.split('\n');
            const previewLines = lines.slice(0, 4).join('\n');
            const remainingLines = lines.length > 4 ? lines.slice(4).join('\n') : null;
            
            const entryDiv = document.createElement('div');
            entryDiv.className = 'mb-4 pb-4 border-b last:border-b-0';
            entryDiv.innerHTML = `
                <h4 class="text-lg font-semibold mb-1">${entry.title}</h4>
                <div class="entry-preview">${previewLines}</div>
                ${remainingLines ? `
                    <div class="entry-full-content hidden">${remainingLines}</div>
                    <div class="flex justify-between items-center mt-2">
                        <div class="read-more-btn" onclick="toggleReadMore(this)"></div>
                        <div class="flex space-x-2" id="goalBtns-container">
                            <button  id="editBtn" onclick="editEntry('${dateStr}', ${entry.journal_id})" class="text-blue-600 hover:text-blue-800">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button id="deleteBtn" onclick="deleteEntry('${dateStr}', ${entry.journal_id})" class="text-red-600 hover:text-red-800">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <button onclick="openJournalPopup('${dateStr}', ${entry.journal_id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                Full View
                            </button>
                        </div>
                    </div>
                ` : `
                    <div class="flex justify-end space-x-2 mt-2">
                        <button id="editBtn" onclick="editEntry('${dateStr}', ${entry.journal_id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button id="deleteBtn" onclick="deleteEntry('${dateStr}', ${entry.journal_id})" class="text-red-600 hover:text-red-800">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `}
            `;
            popupContent.appendChild(entryDiv);
        });
    }
    
    document.getElementById('journalPopup').classList.remove('hidden');
}

// Function to handle read more toggle
function toggleReadMore(button) {
    const container = button.closest('.mb-6, .mb-4');
    const preview = container.querySelector('.entry-preview');
    const fullContent = container.querySelector('.entry-full-content');
    
    if (fullContent.classList.contains('hidden')) {
        preview.style.maxHeight = 'none';
        preview.style.overflow = 'visible';
        preview.classList.remove('entry-preview');
        fullContent.classList.remove('hidden');
        button.textContent = 'Show Less';
    } else {
        preview.style.maxHeight = '6em';
        preview.style.overflow = 'hidden';
        preview.classList.add('entry-preview');
        fullContent.classList.add('hidden');
        button.textContent = 'Read More';
    }
}

// Open form to add new entry
function openNewEntryForm(dateStr) {
    // Get form elements
    const formTitle = document.getElementById('formPopupTitle');
    const entryId = document.getElementById('entryId');
    const entryDate = document.getElementById('entryDate');
    const entryTitle = document.getElementById('entryTitle');
    const entryContent = document.getElementById('entryContent');
    const formPopup = document.getElementById('journalFormPopup');

    // Verify all elements exist
    if (!formTitle || !entryId || !entryDate || !entryTitle || !entryContent || !formPopup) {
        console.error("One or more form elements not found:", {
            formTitle, entryId, entryDate, entryTitle, entryContent, formPopup
        });
        return;
    }

    // Initialize form
    formTitle.textContent = 'New Journal Entry';
    entryId.value = '';
    entryDate.value = dateStr;
    entryTitle.value = '';
    entryContent.value = '';
    formPopup.classList.remove('hidden');
}

// Edit existing entry
function editEntry(dateStr, journalId) {
    const entry = journalEntries[dateStr].find(e => e.journal_id === journalId);
    if (!entry) {
        console.error("Entry not found");
        return;
    }

    // Get form elements
    const formTitle = document.getElementById('formPopupTitle');
    const entryId = document.getElementById('entryId');
    const entryDate = document.getElementById('entryDate');
    const entryTitle = document.getElementById('entryTitle');
    const entryContent = document.getElementById('entryContent');
    const formPopup = document.getElementById('journalFormPopup');

    // Verify all elements exist
    if (!formTitle || !entryId || !entryDate || !entryTitle || !entryContent || !formPopup) {
        console.error("One or more form elements not found:", {
            formTitle, entryId, entryDate, entryTitle, entryContent, formPopup
        });
        return;
    }

    // Populate form
    formTitle.textContent = 'Edit Journal Entry';
    entryId.value = entry.journal_id;
    entryDate.value = dateStr;
    entryTitle.value = entry.title;
    entryContent.value = entry.content;
    formPopup.classList.remove('hidden');
}

// Delete entry
async function deleteEntry(dateStr, journalId) {
    if (confirm('Are you sure you want to delete this entry?')) {
        try {
            const response = await fetch('../../php/journals/delete_entry.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    journal_id: journalId
                })
            });
            
            const data = await response.json();
            
            if (data.status === "success") {
                await fetchEntries();
                updateCalendar();
                closeJournalPopup();
            } else {
                alert('Error deleting entry: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the entry');
        }
    }
}

// Close journal popup
function closeJournalPopup() {
    document.getElementById('journalPopup').classList.add('hidden');
}

// Close form popup
function closeFormPopup() {
    document.getElementById('journalFormPopup').classList.add('hidden');
}

// Save journal entry
async function saveJournalEntry(e) {
    e.preventDefault();
    console.log("Form submitted"); // Add this line
    
    const journalId = document.getElementById('entryId').value;
    console.log("Journal ID:", journalId); // Debug log
    
    const dateStr = document.getElementById('entryDate').value;
    const title = document.getElementById('entryTitle').value.trim();
    const content = document.getElementById('entryContent').value.trim();
    
    if (!title || !content) {
        alert('Please enter both title and content');
        return;
    }
    
    const payload = {
        journal_id: journalId || null,
        title: title,
        content: content,
        entry_date: dateStr
    };
    
    try {
        const response = await fetch('../../php/journals/save_entry.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.status === "success") {
            await fetchEntries();
            updateCalendar();
            closeFormPopup();
            openJournalPopup(dateStr, data.journal_id || journalId);
        } else {
            alert('Error saving entry: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the entry');
    }
}

// Set up event listeners
function setupEventListeners() {
    // Helper function to safely add event listeners
    function addListener(id, event, callback) {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener(event, callback);
        } else {
            console.warn(`Element with ID '${id}' not found`);
        }
    }

    // Month navigation
    addListener('prevMonth', 'click', async () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        await fetchEntries();
        updateCalendar();
    });
    
    addListener('nextMonth', 'click', async () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        await fetchEntries();
        updateCalendar();
    });
    
    // Popup controls
    addListener('closePopup', 'click', closeJournalPopup);
    addListener('closeFormPopup', 'click', closeFormPopup);
    addListener('cancelForm', 'click', closeFormPopup);
    
    // Add new entry from popup
    addListener('addNewEntry', 'click', () => {
        closeJournalPopup();
        if (selectedDate) {
            openNewEntryForm(selectedDate);
        } else {
            console.warn("No date selected");
        }
    });
    
    // Form submission
    const journalForm = document.getElementById('journalEntryForm');
    if (journalForm) {
        journalForm.addEventListener('submit', saveJournalEntry);
    } else {
        console.warn("Journal entry form not found");
    }
    
    // Close popups when clicking outside
    const journalPopup = document.getElementById('journalPopup');
    if (journalPopup) {
        journalPopup.addEventListener('click', (e) => {
            if (e.target === journalPopup) {
                closeJournalPopup();
            }
        });
    }
    
    const formPopup = document.getElementById('journalFormPopup');
    if (formPopup) {
        formPopup.addEventListener('click', (e) => {
            if (e.target === formPopup) {
                closeFormPopup();
            }
        });
    }
}

// Initialize the calendar when page loads
document.addEventListener('DOMContentLoaded', function() {
    initCalendar();
});

// Make functions available globally for HTML onclick attributes
window.openJournalPopup = openJournalPopup;
window.editEntry = editEntry;
window.deleteEntry = deleteEntry;