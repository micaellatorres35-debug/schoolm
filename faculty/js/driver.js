 document.addEventListener('DOMContentLoaded', function () {

        setTimeout(() => {

            if(!localStorage.getItem('isDoneTutorial'))
            {
                introDriver();
            }

        }, 5000);



 })
 

 
 
 
 function introDriver()
    {

        const driver = window.driver.js.driver;

        const driverObj = driver({
        showProgress: true,
        allowClose: false,

        onDestroyed: () => {
            localStorage.setItem('isDoneTutorial', 'true');
        },


        steps: [
            { element: '#dashboard', popover: {description: '<h5>Dashboard</h5><br>This is where you’ll find a summary of all the key information at a glance. Check student counts, recent activity, and upcoming events.'} },
            { element: '#student', popover: {description: '<h5>Student</h5><br>This is where you’ll find a student data and informations.'} },
            { element: '#ocr', popover: { description: '<h5>Image to Text converter</h5><br>A tool that uses ai to convert image to text information.' } },
             { element: '#calendar', popover: { description: '<h5>Calendar</h5><br>A tool that helps user to knows the happenings and upcoming events' } },
            { element: '#profile', popover: { description: '<h5>Profile</h5><br>This section helps user to know and change their information' } },
            { popover: { description: '<h5>Welcome Aboard!</h5><br>You may proceed. Please go ahead and try using the system.' } }
        ]
        });

        driverObj.drive();

    }