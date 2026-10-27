// const ctx = document.getElementById('myAreaChart');

// new Chart(ctx, {
//     type: 'line',
//     data: {
//         labels: ['Jan', 'Mar', 'May', 'Jul', 'Sep', 'Nov', 'Dec'],
//         datasets: [{
//             label: 'Overview',
//             data: [0, 10000, 10000, 15000, 20000, 25000, 40000],
//             tension: 0.4,
//             fill: true,
//             backgroundColor: 'rgba(78,115,223,0.2)',
//             borderColor: 'rgba(78,115,223,1)',
//             pointBackgroundColor: 'rgba(78,115,223,1)',
//             pointBorderColor: '#fff',
//             pointRadius: 4
//         }]
//     },
//     options: {
//         responsive: true,
//         plugins: {
//             legend: {
//                 display: false
//             }
//         },
//         scales: {
//             y: {
//                 beginAtZero: true,
//                 ticks: {
//                     callback: function(value) {
//                         return '$' + value.toLocaleString();
//                     }
//                 }
//             }
//         }
//     }
// });

// const pieCtx = document.getElementById('myPieChart');

// new Chart(pieCtx, {
//     type: 'doughnut',
//     data: {
//         labels: [' Barrowed', 'Damage Equipment', 'Available'],
//         datasets: [{
//             data: [55, 30, 15],
//             backgroundColor: [
//                 '#4e73df',
//                 '#1cc88a',
//                 '#36b9cc'
//             ],
//             hoverOffset: 6
//         }]
//     },
//     options: {
//         cutout: '100%',
//         plugins: {
//             legend: {
//                 display: false
//             }
//         }
//     }
// });


// SUMMARY NUMBER COUNT-UP
document.querySelectorAll(".summary-value .count").forEach((counter, index) => {
  const target = parseInt(counter.dataset.count, 10);

  const counterObject = {
    value: 0,
  };

  gsap.to(counterObject, {
    value: target,

    duration: 1.4,

    delay: 0.55 + index * 0.12,

    ease: "power2.out",

    onUpdate: () => {
      counter.textContent = Math.floor(counterObject.value);
    },

    onComplete: () => {
      counter.textContent = target;
    },
  });
});

document.addEventListener("DOMContentLoaded", () => {
  if (typeof gsap === "undefined") {
    console.warn("GSAP is not loaded.");
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  
// EQUIPMENT STATUS PANEL
  const equipmentPanel = document.querySelector(".dashboard-panel");

  if (equipmentPanel) {
    gsap.from(equipmentPanel, {
      opacity: 0,
      y: 35,
      duration: 0.8,
      ease: "power3.out",
      scrollTrigger: {
        trigger: equipmentPanel,
        start: "top 88%",
        once: true,
      },
    });
  }


// STATUS BOXES
  const statusBoxes = gsap.utils.toArray(".status-box");

  if (statusBoxes.length) {
    gsap.from(statusBoxes, {
      opacity: 0,
      y: 35,
      scale: 0.92,
      duration: 0.65,
      stagger: 0.12,
      ease: "back.out(1.4)",

      scrollTrigger: {
        trigger: ".status-box",
        start: "top 90%",
        once: true,
      },
    });
  }


// STATUS NUMBER COUNT-UP
  document.querySelectorAll(".status-number").forEach((element, index) => {
    const target = parseInt(element.textContent.trim(), 10);

    if (isNaN(target)) return;

    const counter = {
      value: 0,
    };

    element.textContent = "0";

    gsap.to(counter, {
      value: target,

      duration: 1.4,

      delay: 0.35 + index * 0.12,

      ease: "power2.out",

      scrollTrigger: {
        trigger: element,
        start: "top 90%",
        once: true,
      },

      onUpdate: () => {
        element.textContent = Math.floor(counter.value);
      },

      onComplete: () => {
        element.textContent = target;
      },
    });
  });


// STATUS ICON ANIMATION
  document.querySelectorAll(".status-box").forEach((box) => {
    const icon = box.querySelector(".status-icon");

    if (!icon) return;

    gsap.from(icon, {
      scale: 0,
      rotation: -25,
      opacity: 0,
      duration: 0.6,
      delay: 0.15,
      ease: "back.out(2)",

      scrollTrigger: {
        trigger: box,
        start: "top 90%",
        once: true,
      },
    });

    /* Hover */

    box.addEventListener("mouseenter", () => {
      gsap.to(box, {
        y: -6,
        scale: 1.03,
        duration: 0.25,
        ease: "power2.out",
      });

      gsap.to(icon, {
        scale: 1.12,
        rotation: 5,
        duration: 0.3,
        ease: "back.out(1.7)",
      });
    });

    box.addEventListener("mouseleave", () => {
      gsap.to(box, {
        y: 0,
        scale: 1,
        duration: 0.25,
        ease: "power2.out",
      });

      gsap.to(icon, {
        scale: 1,
        rotation: 0,
        duration: 0.25,
        ease: "power2.out",
      });
    });
  });


// EQUIPMENT DISTRIBUTION
  const chartContainer = document.querySelector(".chart-container");

  if (chartContainer) {
    gsap.from(chartContainer, {
      opacity: 0,
      scale: 0.88,
      y: 20,
      duration: 0.9,
      ease: "power3.out",

      scrollTrigger: {
        trigger: chartContainer,
        start: "top 88%",
        once: true,
      },
    });
  }


// PANEL HEADERS
  gsap.utils.toArray(".panel-header").forEach((header) => {
    gsap.from(header, {
      opacity: 0,
      x: -25,
      duration: 0.55,
      ease: "power2.out",

      scrollTrigger: {
        trigger: header,
        start: "top 92%",
        once: true,
      },
    });
  });


// PANEL HEADER ICONS
  document.querySelectorAll(".panel-header i").forEach((icon) => {
    gsap.from(icon, {
      opacity: 0,
      scale: 0,
      rotation: -30,
      duration: 0.5,
      ease: "back.out(1.7)",

      scrollTrigger: {
        trigger: icon,
        start: "top 92%",
        once: true,
      },
    });
  });


// TABLE ROW ANIMATION
  document.querySelectorAll(".dashboard-table").forEach((table) => {
    const rows = table.querySelectorAll("tbody tr");

    gsap.from(rows, {
      opacity: 0,
      x: -25,
      duration: 0.45,
      stagger: 0.08,
      ease: "power2.out",

      scrollTrigger: {
        trigger: table,
        start: "top 90%",
        once: true,
      },
    });
  });

  
// TABLE ROW HOVER
  document.querySelectorAll(".dashboard-table tbody tr").forEach((row) => {
    row.addEventListener("mouseenter", () => {
      gsap.to(row, {
        x: 5,
        duration: 0.2,
        ease: "power2.out",
      });
    });

    row.addEventListener("mouseleave", () => {
      gsap.to(row, {
        x: 0,
        duration: 0.2,
        ease: "power2.out",
      });
    });
  });


// VIEW ALL LINKS
  document.querySelectorAll(".panel-header a").forEach((link) => {
    gsap.from(link, {
      opacity: 0,
      x: 15,
      duration: 0.5,
      delay: 0.15,
      ease: "power2.out",

      scrollTrigger: {
        trigger: link,
        start: "top 92%",
        once: true,
      },
    });

    link.addEventListener("mouseenter", () => {
      gsap.to(link, {
        x: 5,
        duration: 0.2,
        ease: "power2.out",
      });
    });

    link.addEventListener("mouseleave", () => {
      gsap.to(link, {
        x: 0,
        duration: 0.2,
      });
    });
  });


// TABLE HEADER
  document.querySelectorAll(".dashboard-table thead").forEach((header) => {
    gsap.from(header, {
      opacity: 0,
      y: -10,
      duration: 0.4,
      ease: "power2.out",

      scrollTrigger: {
        trigger: header,
        start: "top 94%",
        once: true,
      },
    });
  });


// BADGES
  document.querySelectorAll(".dashboard-table .badge").forEach((badge) => {
    gsap.from(badge, {
      opacity: 0,
      scale: 0.75,
      duration: 0.35,
      ease: "back.out(1.7)",

      scrollTrigger: {
        trigger: badge,
        start: "top 94%",
        once: true,
      },
    });
  });


// REDUCED MOTION
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    ScrollTrigger.getAll().forEach((trigger) => {
      trigger.disable();
    });

    gsap.set(
      [
        ".dashboard-panel",
        ".status-box",
        ".status-icon",
        ".dashboard-table tr",
        ".panel-header",
        ".chart-container",
      ],
      {
        clearProps: "all",
      },
    );
  }

  // REFRESH SCROLLTRIGGER
  window.addEventListener("load", () => {
    ScrollTrigger.refresh();
  });
});
