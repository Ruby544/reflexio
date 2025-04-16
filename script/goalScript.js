// Scroll header behavior
let lastScroll = 0;
const header = document.getElementById('main-header');

window.addEventListener('scroll', () => {
  const currentScroll = window.pageYOffset;
  
  if (currentScroll <= 0) {
    // At top of page - show header
    header.classList.remove('transform', '-translate-y-full');
    return;
  }
  
  if (currentScroll > lastScroll && !header.classList.contains('-translate-y-full')) {
    // Scrolling down - hide header
    header.classList.add('transform', '-translate-y-full');
  } else if (currentScroll < lastScroll && header.classList.contains('-translate-y-full')) {
    // Scrolling up - show header
    header.classList.remove('transform', '-translate-y-full');
  }
  
  lastScroll = currentScroll;
});

// Run displayGoals after page content is loaded
document.addEventListener("DOMContentLoaded", displayGoals);

// Function to open the popup for adding a new goal
function popup() {
    // Create popup container and form elements dynamically
    const popupContainer = document.createElement("div");
    popupContainer.id = "popupContainer";
    popupContainer.className = "fixed inset-0 flex justify-center items-center px-4";
    popupContainer.innerHTML = `
        <div id="popup-content" class=" p-6 sm:p-8 rounded-xl shadow-xl w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Enter a new goal...</h2>
            <form id="goalForm" >
                <label for="goalTitle" class="block text-sm font-medium">Goal Title</label>
                <input type="text" id="goalTitle" name="goalTitle" class="mt-1 w-full p-2 border rounded-lg" required />

                <label for="goalDescription" class="block text-sm font-medium">Goal Description</label>
                <input type="text" id="goalDescription" name="goalDescription" required class="mt-1 w-full p-2 border rounded-lg" />

                <label for="deadline" class="block text-sm font-medium">Deadline</label>
                <input type="date" id="deadline" name="deadline" required class="mt-1 w-full p-2 border rounded-lg" />

                <label for="priority" class="block text-sm font-medium">Priority</label>
                <select id="priority" name="priority" required class="mt-1 w-full p-2 border rounded-lg" />
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
          
                <div id="btn-container"  class="flex justify-end gap-2 pt-4">
                    <button type="submit" id="submitBtn" class="px-4 py-2 rounded-lg">Create Goal</button>
                    <button type="button" id="closeBtn" class="px-4 py-2 rounded-lg">Close</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(popupContainer);
    document.getElementById("goalForm").addEventListener("submit", addGoal);
    document.getElementById("closeBtn").addEventListener("click", closePopup);
}


// Function to open popup for editing a goal
function editGoalPopup(goalId) {
    const popupContainer = document.createElement("div");
    popupContainer.id = "popupContainer";
    popupContainer.className = "fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 px-4";
    popupContainer.innerHTML = `
        <div id="popup-content" class="bg-white p-6 sm:p-8 rounded-xl shadow-xl w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Edit Goal</h2>
            <form id="editGoalForm" >
                <input type="hidden" id="editGoalId" value="${goalId}">
                
                <label for="editGoalTitle">Goal Title</label>
                <input type="text" id="editGoalTitle" name="editGoalTitle" required class="mt-1 w-full p-2 border rounded-lg" />
                
                <label for="editGoalDescription">Goal Description</label>
                <input type="text" id="editGoalDescription" name="editGoalDescription" required class="mt-1 w-full p-2 border rounded-lg" />
                
                <label for="editDeadline">Deadline</label>
                <input type="date" id="editDeadline" name="editDeadline" required class="mt-1 w-full p-2 border rounded-lg" />
                
                <label for="editPriority">Priority</label>
                <select id="editPriority" name="editPriority" required class="mt-1 w-full p-2 border rounded-lg" />
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                
                <div id="btn-container">
                    <button type="submit" id="submitEditBtn" class="submit-btn" class="mt-1 w-full p-2 border rounded-lg" />Save Changes</button>
                    <button type="button" id="closeEditBtn" class="close-btn" class="mt-1 w-full p-2 border rounded-lg" />Close</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(popupContainer);
    document.getElementById("editGoalForm").addEventListener("submit", function(e) {
        e.preventDefault();
        updateGoal(e);
    });
    document.getElementById("closeEditBtn").addEventListener("click", closePopup);
}

// Function to close the popup
function closePopup() {
    const popupContainer = document.getElementById("popupContainer");
    if (popupContainer) {
        popupContainer.remove();
    }
}


let isSubmitting = false; // Global flag to prevent duplicate submissions
// Updated addGoal function in goalScript.js
function addGoal(event) {
    event.preventDefault();

    if (isSubmitting) return; // Prevent multiple submissions
    isSubmitting = true;

     // Get the submit button and disable it
     const submitBtn = document.querySelector('#goalForm button[type="submit"]');
     if (submitBtn) {
         submitBtn.disabled = true;
         submitBtn.textContent = "Saving...";
     }

    // Collect form data
    const formData = {
        goalTitle: document.getElementById("goalTitle").value,
        goalDescription: document.getElementById("goalDescription").value,
        deadline: document.getElementById("deadline").value,
        priority: document.getElementById("priority").value
    };

    // Validate title
    if (!formData.goalTitle.trim()) {
        alert("Goal Title cannot be empty.");
        isSubmitting = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = "Create Goal";
        }
        return;
    }

    // Send data to backend
    fetch("../../php/goals/add_goal.php", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            // Success - close popup and refresh
            closePopup();
            // Use setTimeout to ensure popup is closed before refresh
            setTimeout(() => {
                displayGoals();
            }, 100);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    })
    .finally(() => {
        isSubmitting = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = "Create Goal";
        }
    });
}

// Function to display all goals
function displayGoals() {
    fetch('../../php/goals/get_goals.php')
    .then(response => response.json())
    .then(goals => {
        const goalsList = document.getElementById("goal-list");
        goalsList.innerHTML = "";

        goals.forEach((goal) => {
            const listItem = document.createElement("li");
            listItem.className =
            " rounded-xl p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-md border-l-4 border-purple-500 w-full gap-4";
            listItem.innerHTML = `
                <div class="flex-grow">
                 <p><span style="font-weight: bold;">Title: </span>${goal.title} <span style="font-weight: bold;"> (${goal.priority.toUpperCase()})</p>
                    
                    <p><span style="font-weight: bold;">Description: </span>${goal.description}</p>
                    <p><span style="font-weight: bold;">Deadline: </span> ${goal.deadline || "No deadline set"}</p>
                    
                    <div class="progress-container" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-2">
                        <div class="progress-bar-container w-full h-4 bg-gray-200 rounded-full overflow-hidden">
                            <div class="progress-bar" style="width: ${goal.progress}%"></div> 
                        </div>
                        <span class="progress-percent">${goal.progress}%</span>
                        <div class="progress-buttons">
                            <button class="progress-btn decrease" onclick="updateProgress(${goal.goal_id}, ${goal.progress}, -10)">
                                -10%
                            </button>
                            <button class="progress-btn increase" onclick="updateProgress(${goal.goal_id}, ${goal.progress}, 10)">
                                +10%
                            </button>
                        </div>
                    </div>
                </div>
                <div id="goalBtns-container" class="flex gap-2 mt-4 sm:mt-0">
                    <button id="editBtn" class="px-2 py-1 rounded" onclick="editGoalPopup(${goal.goal_id})">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button id="deleteBtn"  class="px-2 py-1 rounded" onclick="deleteGoal(${goal.goal_id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            `;
            goalsList.appendChild(listItem);
        });
    })
    .catch(error => console.error('Error:', error));
}

// Function to update progress
function updateProgress(goalId, currentProgress, change) {
    // Calculate new progress (ensure it stays between 0-100)
    let newProgress = currentProgress + change;
    newProgress = Math.max(0, Math.min(newProgress, 100));
    
    fetch('http://localhost/ruby/web_app_java/journaling_app/php/goals/updateProgress.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: goalId,
            progress: newProgress
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayGoals(); // Refresh the list to show updated progress
        } else {
            console.error('Error updating progress:', data.error);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to update an existing goal
function updateGoal(event) {
    event.preventDefault();

    const goalId = document.getElementById("editGoalId").value;
    const goalTitle = document.getElementById("editGoalTitle").value.trim();
    const goalDescription = document.getElementById("editGoalDescription").value.trim();
    const deadline = document.getElementById("editDeadline").value.trim();
    const priority = document.getElementById("editPriority").value.trim();

    // Validate required fields
    if (!goalTitle) {
        alert("Goal title cannot be empty");
        return;
    }

    // Create form data object
    const formData = {
        goalId: goalId,
        goalTitle: goalTitle,
        goalDescription: goalDescription,
        deadline: deadline,
        priority: priority
    };

    fetch("/ruby/web_app_java/journaling_app/php/goals/update_goal.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closePopup(document.getElementById("popupContainer"));
            displayGoals(); // Refresh the goals list
        } else {
            throw new Error(data.error || "Failed to update goal");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating goal: ' + error.message);
    });
}

// Function to delete a goal
function deleteGoal(goalId) {
    if (!goalId || isNaN(goalId)) {
        console.error("Invalid goal ID:", goalId);
        alert("Invalid goal ID");
        return;
    }

    if (!confirm("Are you sure you want to delete this goal?")) {
        return;
    }

    fetch(`http://localhost/ruby/web_app_java/journaling_app/php/goals/delete_goal.php?goal_id=${goalId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(message => {
        alert(message);
        displayGoals(); // Refresh the list
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting goal: ' + error.message);
    });
}


