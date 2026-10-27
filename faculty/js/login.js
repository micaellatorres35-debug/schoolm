const toggle = document.querySelector('.password-toggle');
const password = document.getElementById('password');
const icon = document.getElementById('passwordIcon');





toggle.addEventListener('click', function () {

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }

});



document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const alertBox = document.getElementById('alert-box');
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const response = await fetch(`${BASE_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            username,
            password
        })
    });

    const data = await response.json();

    console.log(data);

    if (data.success) {
        
        window.location.href = data.redirect;

    } else {

        switch(data.error_type)
        {
  
            case 'rate_limit':

            
              alertBox.classList.remove('d-none');
              let seconds = data.retry_after;
              alertBox.textContent =  `${data.message} ${data.retry_after} seconds`;

              const timer = setInterval(() => {

                seconds--;

                if (seconds <= 0) {
                    clearInterval(timer);
                    alertBox.textContent = "You can try logging in again.";
                    return;
                }

                alertBox.textContent = `${data.message} ${seconds} seconds`;

             }, 1000);
             
                break;
            case 'invalid_input':
                alertBox.classList.remove('d-none');
                alertBox.textContent =  `${data.message}`;
               break;

        }
    }
});




