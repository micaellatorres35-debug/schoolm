
let stepper;

document.addEventListener('DOMContentLoaded', function () {

    const schedule = document.getElementById('schedule_day');

    stepper = new Stepper(
        document.querySelector('#classStepper'),
        {
            linear: true,
            animation: true,
        }
    );

    if(schedule.value) {
       console.log('test');
    }

    


});


function validateStep1() {

    let subject = document.querySelector('[name="subject_id"]').value;
    let course = document.querySelector('[name="course_id"]').value;
    let section = document.querySelector('[name="section_id"]').value;
    let semester = document.querySelector('[name="semester_id"]').value;

    if (
        subject === '' ||
        course === '' ||
        section === '' ||
        semester === ''
    ) {

         Swal.fire({
                title: "Error!",
                text: "Please input values to missing field/s.",
                icon: "error"
             });
        return;
    }

    stepper.next();
}

function validateStep2()
{

     let teacher = document.querySelector('[name="teacher_id"]').value;
    let room = document.querySelector('[name="room_id"]').value;


    if (
        teacher === '' ||
        room === '' 
     
    ) {

         Swal.fire({
                title: "Error!",
                text: "Please input values to missing field/s.",
                icon: "error"
             });
        return;
    }

    stepper.next();

}





