document.addEventListener("DOMContentLoaded", function () {
    var showTables = document.getElementById("showTables");
    var addRecord = document.getElementById("addRecord");
    var executeQueries = document.getElementById("executeQueries");
    var newQuery = document.getElementById("newQuery");
    var userOptions = document.getElementById("userOptions");
    var submenu = document.getElementById("submenu");

    var addAdvisor = document.getElementById("addAdvisor");
    var addClub = document.getElementById("addClub");
    var addDoSEmployee = document.getElementById("addDoSEmployee");
    var addEvent = document.getElementById("addEvent");
    var addIsMemberOf = document.getElementById("addIsMemberOf");
    var addParticipatesIn = document.getElementById("addParticipatesIn");
    var addReport = document.getElementById("addReport");
    var addStudents = document.getElementById("addStudents");
    var addSupervises = document.getElementById("addSupervises");

    if (userOptions != null){
        var addUser = document.getElementById("addUser");
        var editUser = document.getElementById("editUser");
        var deleteUser = document.getElementById("deleteUser");
        
        userOptions.addEventListener("click", function() {
            if (addUser.style.display == "block") {
                addUser.style.display = "none";
                editUser.style.display = "none";
                deleteUser.style.display = "none";
            } else {
                addUser.style.display = "block";
                editUser.style.display = "block";
                deleteUser.style.display = "block";
            }
        });

        addUser.addEventListener("click", function () {
            window.location.href = "page7.php";
        });
    
        editUser.addEventListener("click", function () {
            window.location.href = "page8.php";
        });
    
        deleteUser.addEventListener("click", function () {
            window.location.href = "page9.php";
        });
    }

    showTables.addEventListener("click", function () {
        window.location.href = "page1.php";
    });

    executeQueries.addEventListener("click", function () {
        window.location.href = "page5.php";
    });

    newQuery.addEventListener("click", function () {
        window.location.href = "page6.php";
    });

    addRecord.addEventListener('mouseenter', () => {
        submenu.style.display = 'block';
    });
    
    addRecord.addEventListener('mouseleave', () => {
        submenu.style.display = 'none';
    });
    
    submenu.addEventListener('mouseenter', () => {
        submenu.style.display = 'block';
    });
    
    submenu.addEventListener('mouseleave', () => {
        submenu.style.display = 'none';
    });

    addAdvisor.addEventListener("click", function () {
        window.location.href = "page2.1.php";
    });

    addClub.addEventListener("click", function () {
        window.location.href = "page2.2.php";
    });

    addDoSEmployee.addEventListener("click", function () {
        window.location.href = "page2.3.php";
    });

    addEvent.addEventListener("click", function () {
        window.location.href = "page2.4.php";
    });

    addIsMemberOf.addEventListener("click", function () {
        window.location.href = "page2.5.php";
    });

    addParticipatesIn.addEventListener("click", function () {
        window.location.href = "page2.6.php";
    });

    addReport.addEventListener("click", function () {
        window.location.href = "page2.7.php";
    });

    addStudents.addEventListener("click", function () {
        window.location.href = "page2.8.php";
    });

    addSupervises.addEventListener("click", function () {
        window.location.href = "page2.9.php";
    });

});