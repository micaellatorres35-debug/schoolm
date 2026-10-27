document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('homeCalendar');

    loadRecentEnrollees();

  // enrolled students

  fetch(`${BASE_URL}/students/count`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.totalActiveStudent, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("enrolled").textContent = obj.val.toLocaleString();
      }
    });
  });

  // subject offered 

   fetch(`${BASE_URL}/students/subCount`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.totalSubject, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("subject-offered").textContent = obj.val.toLocaleString();
      }
    });

    
  });

   // cor request 

   fetch(`${BASE_URL}/students/CountCOR`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.total_pending_cor, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("corRequests").textContent = obj.val.toLocaleString();
      }
    });

    
  });

  // tor request 

   fetch(`${BASE_URL}/students/CountTOR`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.total_pending_tor, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("torRequests").textContent = obj.val.toLocaleString();
      }
    });

    
  });


  // totalStudents


   fetch(`${BASE_URL}/students/count`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.totalActiveStudent, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("totalStudents").textContent = obj.val.toLocaleString();
      }
    });

    
  });

  

   // curriculum


   fetch(`${BASE_URL}/students/CountCurriculum`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.total_active_curriculums, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("curriculums").textContent = obj.val.toLocaleString();
      }
    });

    
  });

   // offeredPrograms


   fetch(`${BASE_URL}/students/CountCourse`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.total_courses, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("offeredPrograms").textContent = obj.val.toLocaleString();
      }
    });

    
  });
 
 

  


  // enrollee count


   fetch(`${BASE_URL}/students/enrolleeCount`)
  .then(response => response.json())
  .then(result => {
    const obj = { val: 0 }; 

    gsap.to(obj, {
      val: result.totalEnrollee, 
      duration: 2,                   
      ease: "power1.out",
      roundProps: "val",             
      onUpdate: () => {
        document.getElementById("enrollee").textContent = obj.val.toLocaleString();
      }
    });

    
  });
   


  

  const progress = { value: 0 };

  gsap.to(progress, {
    value: cpu,
    duration: 2, 
    onUpdate: () => {
      document.getElementById("cpuBar").style.width = progress.value + "%";
    },
    ease: "power1.out"
  });

    const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          height: 400,
          aspectRatio: 1.35, 

        headerToolbar: {
             left: 'prev,next today',
             center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        selectable:true,
         eventSources: [
           
            {
            events: function(info, successCallback, failureCallback) {
                var year = info.start.getFullYear();
                fetch(`https://date.nager.at/api/v3/PublicHolidays/${year}/PH`)
                .then(res => res.json())
                .then(data => {
                    const holidays = data.map(h => ({ title: h.name, start: h.date, allDay: true, color: 'red', description: h.name, type: 'holiday' }));
                    successCallback(holidays);
                });
            }
            },
            {
                id: 'db-events',          
                url: `${BASE_URL}/calendar/events`,
                method: 'GET',
                failure: function() {
                    alert('There was an error fetching events!');
                }
            }
        ],

         buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day'
            },

    });

    calendar.render()



      /*  ========================================================================================= 
       |                                                                                         |
       |     Activity Chart                                                                 |
       |                                                                                         |
       ========================================================================================= 
    */

  const ctx = document.getElementById('activityChart').getContext('2d');

// Initialize empty Chart.js line chart
let activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Activity',
            data: [], 
            borderWidth: 2,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#eee' } },
            x: { grid: { display: false } }
        }
    }
});

  updateChart();

  // Handle period change
  document.getElementById('period').addEventListener('change', function() {
      const period = this.value;
      updateChart(period);
  });


  function updateChart(periodDays = '7days') {
    
    fetch(`${BASE_URL}/activity/api?range=${periodDays}`) 
        .then(res => res.json())
        .then(data => {

          const countsByDate = data.reduce((acc, item) => {
                const date = item.created_at.split(' ')[0]; 
                acc[date] = (acc[date] || 0) + 1;
                return acc;
            }, {});

            const sortedLabels = Object.keys(countsByDate).sort((a, b) => new Date(a) - new Date(b));


            const sortedCounts = sortedLabels.map(label => countsByDate[label]);
           
            activityChart.data.labels = sortedLabels; 
            activityChart.data.datasets[0].data = sortedCounts; 
            
            activityChart.update();

          
           
        })
        .catch(err => console.error('Error fetching activity:', err));
    }

    navigator.storage.estimate().then(data => {
    console.log("Used:", data.usage);
    console.log("Quota:", data.quota);
    });

  
 });




async function loadRecentEnrollees() {

    const tbody = document.getElementById('recentEnrollees');

    try {

        const response = await fetch(`${BASE_URL}/students/all`);

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();

        const enrollees = result.data || [];

        tbody.innerHTML = '';

        if (enrollees.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        No recent enrollees found.
                    </td>
                </tr>
            `;

            return;
        }

        enrollees.forEach(student => {

            const fullName = [
                student.first_name,
                student.middle_name,
                student.surname,
                student.suffix
            ]
            .filter(value => value && value.trim() !== '')
            .join(' ');

            const yearLevel = formatYearLevel(student.year_level);

            const enrolledDate = formatDate(student.enrolled_at);

            const status = student.enrollment_status || 'Unknown';

            tbody.innerHTML += `
                <tr>

                    <td class="px-4 fw-semibold">
                        ${escapeHtml(student.student_number)}
                    </td>

                    <td>
                        <div class="d-flex align-items-center">

                            <div class="bg-primary bg-opacity-10
                                        text-primary rounded-circle
                                        d-flex align-items-center
                                        justify-content-center me-2"
                                 style="width: 36px; height: 36px;">

                                <i class="bi bi-person-fill"></i>

                            </div>

                            <div>

                                <div class="fw-semibold">
                                    ${escapeHtml(fullName)}
                                </div>

                                <small class="text-muted">
                                    ${escapeHtml(student.email || 'No email')}
                                </small>

                            </div>

                        </div>
                    </td>

                    <td>
                        ${escapeHtml(student.course_name || 'N/A')}
                    </td>

                    <td>
                        ${yearLevel}
                    </td>

                    <td>
                        ${enrolledDate}
                    </td>

                    <td>
                        <span class="badge bg-success">
                            ${escapeHtml(status)}
                        </span>
                    </td>

                </tr>
            `;

        });

    } catch (error) {

        console.error('Failed to load recent enrollees:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    Failed to load recent enrollees.
                </td>
            </tr>
        `;
    }
}

function formatYearLevel(year) {

    const levels = {
        1: '1st Year',
        2: '2nd Year',
        3: '3rd Year',
        4: '4th Year'
    };

    return levels[year] || `${year}th Year`;
}


function formatDate(dateString) {

    if (!dateString) {
        return 'N/A';
    }

    const date = new Date(dateString.replace(' ', 'T'));

    return date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}


function escapeHtml(value) {

    const div = document.createElement('div');

    div.textContent = value ?? '';

    return div.innerHTML;
}



 