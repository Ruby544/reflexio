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


// Function to open habit creation/editing popup
function popup(habitId = null) {
    const popupContainer = document.createElement("div");
    popupContainer.id = "popupContainer";
    popupContainer.className = "fixed inset-0 flex items-center justify-center bg-sky-500/50 bg-opacity-50";

    // The popup when user clicks on "+ Add Habit" button
    popupContainer.innerHTML = `
        <div class="popup-content bg-white p-6 rounded-lg shadow-lg w-full max-w-md sm:max-w-lg md:max-w-xl lg:max-w-2xl p-6 md:p-8"> <br><br>
            <h1 class="text-2xl font-bold mb-4">${habitId ? "Edit Habit" : "Set New Habit"}</h1>
            <form id="habitForm">
                <label for="habitTitle" class="block font-semibold">Habit Title</label>
                <input type="text" id="habitTitle" name="habitTitle" class="w-full border-1 border-gray-300 p-3 rounded focus:outline-none focus:ring focus:ring-blue-400 " style="border:1px; border-style:solid; border-color: gray;"required> <br>

                <label for="numberOfTimes" class="block font-semibold">How many times monthly?</label>
                <input type="number" id="numberOfTimes" name="numberOfTimes" class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring focus:ring-blue-400"  style="border:1px; border-style:solid; border-color: gray;" required><br>
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <button type="submit" id="submitBtn" class="flex-1 bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition mb-8">
                        ${habitId ? "Save Changes" : "Create Habit"}
                    </button>
                    <button type="button" id="closeBtn" onclick="closePopup()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Close
                    </button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(popupContainer);

    // Attach `addHabit()` to the form submit event
    document.getElementById("habitForm").addEventListener("submit", addHabit);
}

// Close the habit creation/editing popup
function closePopup() {
    const popupContainer = document.getElementById("popupContainer");
    if (popupContainer) popupContainer.remove();
}

// Add new habit
function addHabit(event) {
    event.preventDefault(); // Prevent form from refreshing the page

    const habitTitle = document.getElementById("habitTitle").value.trim();
    const numberOfTimes = document.getElementById("numberOfTimes").value.trim();

    // Check if required fields are filled
    if (!habitTitle || !numberOfTimes) {
        alert("Please fill in all fields.");
        return;
    }

    // Send data to the server to add the new habit
    fetch("../../php/habits/add_habit.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            habitTitle: habitTitle,
            numberOfTimes: numberOfTimes
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("✅ Habit added:", data);
        if (data.status === "success") {
            closePopup(); // Close popup on success
            displayHabits(); // Refresh habit table
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error adding habit:", error);
    });
}

// Display habits based on selected month
function displayHabits() {
    fetch("../../php/habits/display_habits.php")
    .then(response => response.json()) // Parse JSON response
        .then(data => {
            console.log("✅ Received data:", data);

            // Ensure `habits` is always an array
            let habits = Array.isArray(data.habits) ? data.habits : Object.values(data.habits);

            // If habits is not an array, reset to an empty array
            if (!Array.isArray(habits)) {
                console.error("❌ habits is not an array!", habits);
                habits = []; // Default to an empty array
            }

            console.log("📌 Final habits array:", habits);

            // Clear previous habit rows
            const habitRows = document.getElementById("habit-rows");
            habitRows.innerHTML = "";


            // ✅ 🚀 Force the correct background color, current month and year for habit tracking
            const currentMonthYear = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;

            habits.forEach(habit => {
                console.log("📌 Processing habit:", habit);

                // Ensure completed_days is always an object
                const completedDays = habit.completed_days ? habit.completed_days : {};
                const completedThisMonth = Array.isArray(completedDays[currentMonthYear]) ? completedDays[currentMonthYear]: [];

                // Create a new row for each habit
                const row = document.createElement("tr");
         row.innerHTML = `
                <td class="py-2 px-4 text-sm sm:text-base lg:text-lg">${habit.title} <br>(${habit.times_monthly} times)</td>
                ${Array.from({ length: new Date(currentYear, currentMonth + 1, 0).getDate() }, (_, i) => {
                    const day = i + 1;
                    const isCompleted = completedThisMonth.includes(String(day));
                    return `
           <td class="text-center">
         <div class="habit-cell           ${isCompleted ? "bg-sky-500/50" : ""}" 
             data-day="${day}" 
             data-habit-id="${habit.habit_id}"
             ">
             </div>
           </td>
          `;     
                }).join("")}
                <td class="py-2 px-4 flex justify-center items-center space-x-2"  >
                <button id="editBtn" onclick="editHabitPopup(${habit.habit_id}, '${habit.title}', ${habit.times_monthly})" class="text-blue-500 hover:text-blue-700"><i class="fa-solid fa-pen" ></i></button>
                 <button id="deleteBtn"  class="px-2 py-1 rounded" onclick="deleteHabit(${habit.habit_id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
                `;
                habitRows.appendChild(row);  // Append row to the table

                // Check if the habit's goal is met for the current month
            const completedCount = completedThisMonth.length;
            const goal = habit.times_monthly;

            if (completedCount >= goal) {
                row.style.backgroundColor = "#C8E6C9"; // ✅ Light pastel green
                console.log(`Row color set to green for habit: ${habit.title}`);
            } else {
                row.style.backgroundColor = ""; // ✅ Reset if below goal
            }

  // Attach click events to toggle cells
  row.querySelectorAll(".habit-cell").forEach(cell => {
    cell.addEventListener("click", toggleHabitCompletion);

    const day = cell.getAttribute("data-day");
    const habitId = cell.getAttribute("data-habit-id");

    console.log(`Fetching habit status for habit ${habitId} on day ${day}`);

    // Construct the URL for get_habit_status.php
    const url = `../../php/habits/get_habit_status.php?habit_id=${habitId}&day=${day}&month_year=${currentMonthYear}`;

                fetch(url)
                    .then(response => response.json())
                    .then(status => {
                        if (status.completed) {
                            cell.style.backgroundColor = "rgb(74, 112, 226)"; // Light blue color
                        } else {
                            cell.style.backgroundColor = ""; // Reset color when untoggled
                        }

                        if (status.is_completed_for_month) {
                            row.style.backgroundColor = "#C8E6C9"; // ✅ Light pastel green
                        } else {
                            row.style.backgroundColor = ""; // ✅ Reset if below goal
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching habit status:", error);
                    });
            });
        });
    })
    .catch(error => {
        console.error("Error fetching habits:", error);
    });
}
      
// Toggle habit completion days for specific month
function toggleHabitCompletion(event) {
    if (!event.target.classList.contains("habit-cell")) return;  

    const day = event.target.getAttribute("data-day") || "";
    const habitId = event.target.getAttribute("data-habit-id") || "";
    const currentMonthYear = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;

    if (!habitId || !day || !currentMonthYear) {
        console.error("❌ ERROR: Missing habitId, day, or month_year.");
        return;
    }

    // Toggle cell colour when clicked
    if (event.target.style.backgroundColor === "rgb(74, 112, 226)") {
        event.target.style.backgroundColor = "";
    } else {
        event.target.style.backgroundColor = "rgb(74, 112, 226)"; // Apply colour  when habit is completed
    }

    // Send the status update to the backend
    fetch("../../php/habits/toggle_habit.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `habitId=${encodeURIComponent(habitId)}&day=${encodeURIComponent(day)}&currentMonthYear=${encodeURIComponent(currentMonthYear)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);
        if (data.success) {
            console.log(`✅ Habit ${habitId} updated for day ${day}`);
          
           updateHabitRowColor(habitId); // ✅ Update the row color based on habit completion count
        } else {
            console.error("❌ ERROR:", data.error);
        }
    })
    .catch(error => console.error("❌ Fetch error:", error));
}

// Update row color based on habit progress
function updateHabitRowColor(habitId) {
    console.log("Checking row color for habit:", habitId); // Debugging

    const currentMonthYear = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;
    document.querySelectorAll(".habit-cell").forEach(cell => {
    cell.addEventListener("click", toggleHabitCompletion);
    
    const day = cell.getAttribute("data-day") || "";
    
    fetch(`../../php/habits/get_habit_status.php?habit_id=${habitId}&day=${day}&month_year=${currentMonthYear}`)
    .then(response => response.json())
        .then(data => {
        console.log("Habit Progress Data:", data);

        const row = document.querySelector(`div[data-habit-id="${habitId}"]`).closest("tr");
        if (row) {
            if (data.completed_count >= data.goal) {
                row.style.backgroundColor = "#C8E6C9"; // Light pastel green
            } else {
                row.style.backgroundColor = ""; // Reset if below goal
            }
        }
    })
    .catch(error => console.error("❌ Error fetching habit progress:", error));
});
}

// Edit habit popup
function editHabitPopup(habitId, existingTitle, existingTimes) {
    const popupContainer = document.createElement("div");
    popupContainer.id = "popupContainer";
    popupContainer.className = "fixed inset-0 flex items-center justify-center bg-sky-500/50 bg-opacity-50";

    // Set up the editing popup
    popupContainer.innerHTML = `
        <div class="popup-content bg-white p-6 rounded-lg shadow-lg w-full max-w-md sm:max-w-lg md:max-w-xl lg:max-w-2xl p-6 md:p-8"> <br><br>
            <h1 class="text-2xl font-bold mb-4"> Edit Habit </h1>
            <form id="editHabitForm">
            <input type="hidden" id="editHabitId" value="${habitId}">

                <label for="editHabitTitle" class="block font-semibold">Habit Title</label>
                <input type="text" id="editHabitTitle" name="editHabitTitle" class="w-full border p-3 rounded " style="border:1px; border-style:solid; border-color: gray;" required> <br>

                <label for="editNumberOfTimes" class="block font-semibold">How many times monthly?</label>
                <input type="number" id="editNumberOfTimes" name="editNumberOfTimes" class="w-full border p-3 rounded" style="border:1px; border-style:solid; border-color: gray;" required><br>
                
                <div class="flex justify-between">
                    <button type="submit" id="submitBtn" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition mb-8">
                        Save Changes
                    </button>
                    <button type="button" id="closeBtn" onclick="closePopup(popupContainer)" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Close
                    </button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(popupContainer);

    // Attach `editHabit()` to the form submit event
    document.getElementById("editHabitForm").addEventListener("submit", updateHabit);
}

// Update an existing habit
function updateHabit(event) {
    event.preventDefault();

    // Get values from form inputs
    const habitId = document.getElementById("editHabitId").value;
    const habitTitle = document.getElementById("editHabitTitle").value.trim();
    const numberOfTimes = document.getElementById("editNumberOfTimes").value.trim();

    // Validate input
    if (!habitTitle || !numberOfTimes) {
        alert("❌ ERROR: Missing data.");
        return;
    }

    // Send updated habit data to the server using POST
    fetch("../../php/habits/update_habit.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `habitId=${encodeURIComponent(habitId)}&habitTitle=${encodeURIComponent(habitTitle)}&numberOfTimes=${encodeURIComponent(numberOfTimes)}`
    })
    .then(response => response.text()) // Convert response to plain text
    .then(data => {
        console.log("Server response:", data);
        if (data.includes("successfully")) {
            alert("Habit updated successfully!");
            displayHabits(); // Refresh the habits list
            closePopup("editPopupContainer"); // Close the popup
        } else {
            alert("❌ Error: " + data); // Show error message
        }
    })
    .catch(error => console.error("❌ Fetch Error:", error));
}

// Delete a habit
function deleteHabit(habitId) {
   // Validate the habit ID
    if (!habitId || habitId === 0) {
        console.error("❌ ERROR: Invalid habit ID:", habitId);
        return;
    }

    // Confirm before deletion
    if (!confirm("Are you sure you want to delete this habit?")) {
        return;
    }

    // Send request to delete the habit via GET
    fetch(`../../php/habits/delete_habit.php?habit_id=${habitId}`, {
        method: "GET"
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Show server response
        displayHabits(); // Refresh the habit list after deletion
    })
    .catch(error => console.error("❌ ERROR:", error));
}

// Function to close the completion popup
window.closeCompletionPopup = function () {
    const popupContainer = document.getElementById("completionPopup");
    if (popupContainer) {
        popupContainer.remove(); // Remove the popup from the DOM
    }
};

// Update calendar when switching months
function updateCalendar(year, month) { 
    const headerRow = document.getElementById("days-header");    // The calendar header row
    const monthYearText = document.getElementById("month-year"); // The text displaying current month/year
    
    // Reset header and add the first "Habits" column
    headerRow.innerHTML = '<th class="text-left py-2 table-auto" style="column:width:50px">Habits</th>';

    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

    // Display month's name
    monthYearText.textContent = new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' });

    // Generate days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const th = document.createElement("th");
        th.className = "text-center py-2 px-1 md:px-2 text-xs md:text-sm";
        th.textContent = day;

        // Highlight the current day
        if (isCurrentMonth && day === today.getDate()) {
            th.style.backgroundColor = "#222"; // Dark background
            th.style.color = "white"; // White text
           
            th.style.borderRadius = "5px"; // Optional rounded corners
        }
        headerRow.appendChild(th); // Add to header
    }

    displayHabits(); // Re-render habits for the current month
}

// Initialize calendar on page load
let currentDate = new Date();
let currentYear = currentDate.getFullYear();
let currentMonth = currentDate.getMonth();
updateCalendar(currentYear, currentMonth);  // Draw calendar for current month

// Month navigation
document.getElementById("prevMonth").addEventListener("click", () => {
    currentMonth = (currentMonth - 1 + 12) % 12;  // Go to previous month
    if (currentMonth === 11) currentYear--;      // Decrement year if we cross to previous December
    updateCalendar(currentYear, currentMonth);   // Update calendar display
}); 

// Handle clicking the "next month" button
document.getElementById("nextMonth").addEventListener("click", () => {
    currentMonth = (currentMonth + 1) % 12;    // Go to next mont
    if (currentMonth === 0) currentYear++;     // Increment year if we move to January
    updateCalendar(currentYear, currentMonth); // Update calendar display
});
