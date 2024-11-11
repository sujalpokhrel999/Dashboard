const addBtn = document.getElementById('addBtn');
const clearBtn =document.getElementById('clearBtn');
let inputField = document.getElementById('inputField');
const deleteBtns = document.querySelectorAll('.delete');

// Loop through each delete button and add a click event listener
deleteBtns.forEach((deleteBtn) => {
    deleteBtn.addEventListener('click', (event) => {
        // Find the closest parent with the class 'assignment-card' and remove it
        const assignmentCard = event.target.closest('.assignment-card');
        if (assignmentCard) {
            assignmentCard.remove();
            console.log('Task deleted');
        }
    });
});


//Clears the input field 
clearBtn.addEventListener('click',()=>{
    inputField.value='';
    let selectedPriority = document.getElementById('priorityDropdown').value='Level';
    document.getElementById('level').textContent = selectedPriority;
    console.log('ok');
    updatePriority();
});


//adds a new task in the assignment section 
addBtn.addEventListener('click',()=>{
    let assignmentsSection = document.getElementById('assignments');
    let inputData = inputField.value;

    if(inputField.value !== ''){
    const assignmentCard = document.createElement("div");
    assignmentCard.classList.add("assignment-card");
    assignmentCard.id ="assignment-card";


    // Create a <p> element for the task name and add text
    const timestamp = document.createElement("div");
    timestamp.classList.add('taskHead');
    const taskName = document.createElement("p");
    taskName.textContent = inputData; // Replace "New Task" with the input field value
    taskName.id="asd";
    const time = document.createElement('p');
    time.textContent="1:00-5:00";
    time.id="timeStamp";
    timestamp.appendChild(taskName);
    timestamp.appendChild(time);
    assignmentCard.appendChild(timestamp);


    //creating the time stamp

    // Create a <span> element for the status and add text and style
    const status = document.createElement("span");
    status.classList.add("status");
    status.textContent = "Incomplete";
    status.style.backgroundColor = ""; // You can leave it blank, as it will show as default or add a specific color for incomplete
    assignmentCard.appendChild(status);


    //include the delete icon

      // Create a new img element
      const icon = document.createElement("img");

      // Set the attributes for the img element
      icon.src = "https://img.icons8.com/fluency-systems-regular/20/filled-trash.png";
      icon.alt = "filled-trash";
      icon.width = 20;
      icon.height = 20;
      icon.id = "delete"; // Optional, if you need to reference this icon later
      icon.classList.add('delete');
  
      // Append the icon to the container
      assignmentCard.appendChild(icon);


      //adding functionality to the icon
      icon.addEventListener('click', () => {
        assignmentCard.remove();
        console.log('Task deleted');
      });


    // Append the new assignment card to the assignments section
    assignmentsSection.appendChild(assignmentCard);
    inputField.value='';
    let selectedPriority = document.getElementById('priorityDropdown').value='Level';
    document.getElementById('level').textContent = selectedPriority;
    updatePriority();
    }

    else{
        alert('empty input field');
        let selectedPriority = document.getElementById('priorityDropdown').value='Level';
        document.getElementById('level').textContent = selectedPriority;
        updatePriority();
    }
});



/*----------------------------------Priority Level-------------*/ 
function updatePriority() {
    // Get the selected value from the dropdown
    const selectedPriority = document.getElementById('priorityDropdown').value;
    console.log(selectedPriority);
    // Update the text in the <p> tag with id="level" to reflect the selected option
    document.getElementById('level').textContent = selectedPriority;
}

// Set initial value when page loads
document.addEventListener('DOMContentLoaded', updatePriority);