
function status(){
// Select all elements with the class 'status'
let statusElements = document.querySelectorAll('.status');
// Loop through each element and attach a click event listener
statusElements.forEach((statsValue) => {
    statsValue.addEventListener('click', () => {
        console.log('Status clicked');
        if (statsValue.innerText === 'Completed') {
          statsValue.innerText = "Incomplete";
            statsValue.style.backgroundColor = '#4F46E5'; // Change background for 'Incomplete'
        } else {
            statsValue.innerText = "Completed";
            statsValue.style.backgroundColor = '#10B981'; // Change background for 'Completed'
        }
    });
});
};
status();

document.getElementById('newTaskBtn').addEventListener('click', function() {
    const modal = document.getElementById('myModal');
    let addTask = document.getElementById('addTask');
    let taskDiv = document.createElement('div');
    const addBtn = document.getElementById('addBtn');
    const clearBtn = document.getElementById('clearBtn');
    const close = document.getElementById('close');
    modal.style.display="block";
    //close btn action
    close.addEventListener('click',()=>{
        modal.style.display="none";
        status();
    });
    //Clear Btn action
    clearBtn.addEventListener('click',()=>{
        const ClearData = document.getElementById('addTask').value;
        if(ClearData!==''){
        document.getElementById('addTask').value = '';
        console.log("clickedd");
        }
        status();
    });


// Add button action
addBtn.addEventListener('click', () => {
    const taskData = document.getElementById('addTask').value;
    console.log(taskData);
    if (taskData === "") {
        alert("Input needed");
    } else {
        let taskDiv = document.createElement('div');
        taskDiv.classList.add('assignment-card');

        // Set inner HTML for the new task with status as Incomplete
        taskDiv.innerHTML = `
            <p>${taskData}</p>
            <span class="status">Incomplete</span>
        `;

        // Append the new task to the assignments section
        document.querySelector('.assignments').appendChild(taskDiv);

        // Clear the input field
        document.getElementById('addTask').value = '';
    }
    status();
}); // <-- Closing parenthesis and semicolon for addEventListener function
});



// const linkElements = document.querySelectorAll('.links'); // Select all elements with class 'links'

// linkElements.forEach(link => {
//     link.addEventListener('click', function() {
//         // Remove 'active' class from all links
//         linkElements.forEach(l => l.classList.remove('active'));
//         console.log("unclicked")
        
//         // Add 'active' class to the clicked link
//         this.classList.add('active');

//     });
// });

