const startHour = 7;
const endHour = 19;
const days = ['Mon','Tue','Wed','Thu','Fri','Sat'];


const params = new URLSearchParams(window.location.search);
let section_id = params.get('section_id') || 34;

const sectionSelect = document.getElementById("section");
if (sectionSelect && section_id) {
    sectionSelect.value = section_id;
}

getData(section_id);

document.getElementById("section").addEventListener('change', (e) => {

        section_id = e.target.value;
        getData(section_id);

});



document.addEventListener("click", function(e) {

    if (e.target.closest(".schedule-btn")) {

        const showScheduleDetailModal = new bootstrap.Modal(document.getElementById('showScheduleDetailModal'));
        const card = e.target.closest(".schedule-btn");
        const id = card.dataset.id;

           fetch(`${BASE_URL}/section-schedule/${id}`)
        .then(response => response.json())
         .then(result => {
          
            // course info
            document.getElementById('show_course_code').textContent = result.course_code;
            document.getElementById('show_course_name').textContent = result.course_name;
            document.getElementById('show_section_name').textContent = result.section_name;
            document.getElementById('show_year_level').textContent = result.year_level;

            // subject info
            document.getElementById('show_subject_code').textContent = result.subject_code;
            document.getElementById('show_subject_name').textContent = result.subject_name;
            document.getElementById('show_semester').textContent = result.semester_name;
            document.getElementById('show_teacher_name').textContent = result.teacher_name;
            document.getElementById('show_units').textContent = result.units;  
            document.getElementById('show_lecture_hours').textContent = result.lec_hours;
            document.getElementById('show_laboratory_hours').textContent = result.lab_hours;
            

            // schedule and class info
            document.getElementById('show_room').textContent = result.room_name;
            document.getElementById('show_room_type').textContent = result.room_type;
            document.getElementById('show_building').textContent = result.building_name;
            document.getElementById('show_day').textContent = result.schedule_day;
            document.getElementById('show_start_time').textContent = result.start_time ;
            document.getElementById('show_end_time').textContent = result.end_time ;

             document.getElementById("class_offer_card").addEventListener("click", function () {
                
                const course_id = result.course_id;
                const semester = result.semester_id
                const year_level = result.year_level_int;
                const section_id = result.section_id;

               window.location.href = `${BASE_URL}/class-offering?year_level=${year_level}&section=${section_id}&course_id=${course_id}&semester=${semester}`;

             });

      
           showScheduleDetailModal.show();


         });


        


    }
});


   document.getElementById('pdf').addEventListener('click',()=>{

        const section_id = document.getElementById("section").value;
    
         getPdf(section_id);

    });


function generateGrid() {
    let html = '';

    for (let hour = startHour; hour < endHour; hour++) {
        for (let half = 0; half < 2; half++) { 
            html += `<tr>`;
            if (half === 0) html += `<td rowspan="2">${formatTime(hour)} - ${formatTime(hour+1)}</td>`;
            days.forEach(day => {
                html += `<td class="grid-cell" data-day="${day}" data-hour="${hour}" data-half="${half}"></td>`;
            });
            html += `</tr>`;
        }
    }

    document.getElementById('grid-body').innerHTML = html;
}

function formatTime(hourFloat) {
    const hour = Math.floor(hourFloat);
    const min = hourFloat % 1 === 0 ? '00' : '30';
    let suffix = hour >= 12 ? 'PM' : 'AM';
    let displayHour = hour % 12 || 12;
    return `${displayHour}:${min} ${suffix}`;
}

function formatSchedule(timeStr) {
    if (!timeStr) return '';
    const [hourStr, minStr] = timeStr.split(':');
    let hour = parseInt(hourStr, 10);
    const minute = minStr.padStart(2, '0');
    const suffix = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12; 
    return `${displayHour}:${minute} ${suffix}`;
}

function fillGrid(schedule) {


     document.getElementById('grid-body').innerHTML = '';
   
    generateGrid();

    schedule.forEach(item => {
        const [startH, startM] = item.start_time.split(':').map(Number);
        const [endH, endM] = item.end_time.split(':').map(Number);

        let totalHalfHours = (endH - startH) * 2 + (endM - startM) / 30;

       
        const firstCell = document.querySelector(
            `.grid-cell[data-day="${item.day.substring(0,3)}"][data-hour="${startH}"][data-half="${startM >= 30 ? 1 : 0}"]`
        );



        if (firstCell) {
           firstCell.innerHTML = `
                    <div class="btn btn-sm btn-primary schedule-btn" data-id="${item.class_offering_id}" >
                        <h6 class="card-title mb-0">${item.section_name}</h6>
                        <p class="card-text mb-0"><small>${item.subject_code}</small></p>
                        <p class="card-text mb-0"><small>${item.subject_name || ''}</small></p>
                         <p class="card-text mb-0"><small>${item.day}</small></p>
                        <p class="card-text mb-0"><small>${formatSchedule(item.start_time)} - ${formatSchedule(item.end_time)}</small></p>
                        <p class="card-text mb-0"><small>${item.room_name}</small></p> 
                        <p class="card-text mb-0"><small>${item.teacher_name}</small></p> 
                    </div>
            `;
            firstCell.style.backgroundColor = '#0d6efd';
            firstCell.style.textAlign = 'center';
            firstCell.style.verticalAlign = 'middle';
            firstCell.setAttribute('rowspan', totalHalfHours); 


            let currentHour = startH;
            let currentHalf = startM >= 30 ? 1 : 0;

            for (let i = 1; i < totalHalfHours; i++) {
                currentHalf++;
                if (currentHalf > 1) {
                    currentHalf = 0;
                    currentHour++;
                }
                const cell = document.querySelector(
                    `.grid-cell[data-day="${item.day.substring(0,3)}"][data-hour="${currentHour}"][data-half="${currentHalf}"]`
                );
                if (cell) cell.remove();
            }
        }
    });
}


 function getPdf(section_id)
    {

        //  fetch(`${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`)
        // .then(response => response.blob())
        // .then(blob => {
        //     const url = URL.createObjectURL(blob);
        //     window.open(url); 
        // })
        // .catch(err => console.error(err));
        

        // take this if you dont want redirection confirmation 

        window.open(
        `${BASE_URL}/section-schedule/pdf?section_id=${section_id}`,
        "_blank"
        );
    }




function getData(section_id) {

    fetch(`${BASE_URL}/section-schedule/all?section_id=${section_id}`)
        .then(res => res.json())
        .then(data => {
            const scheduleToFill = Array.isArray(data) ? data : (data.schedule || []);
            fillGrid(scheduleToFill);
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            generateGrid(); 
        });
}



