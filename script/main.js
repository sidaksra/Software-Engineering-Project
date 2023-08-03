document.addEventListener("DOMContentLoaded", () => {
    var navItems = document.querySelectorAll(".navLink")

    for (var i = 0; i < navItems.length; i++) {
        navItems[i].addEventListener("click", function () {
            this.classList.add("active")
        })
    }
})


function printGraphContainer() {
    const dataUrl = document.getElementById("myLineChart").toDataURL()
    console.log(dataUrl)

    let windowContent = "<!DOCTYPE html>"
    windowContent += "<html>"
    windowContent += "<head><title>Print canvas</title></head>"
    windowContent += "<body>"
    windowContent += '<img src="' + dataUrl + '"/>'
    windowContent += "</body>"
    windowContent += "</html>"

    const printWin = window.open(
        "",
        "",
        "width=" + screen.availWidth + ",height=" + screen.availHeight
    )
    printWin.document.open()
    printWin.document.write(windowContent)

    printWin.document.addEventListener(
        "load",
        function () {
            printWin.focus()
            printWin.print()
            printWin.document.close()
            printWin.close()
        },
        true
    )
}


function checkAllRows(checkBox) {
    get = document.querySelectorAll(".rowCheckbox")
    for (var i = 0; i < get.length; i++) {
        get[i].checked = checkBox.checked
    }
}

window.onload = function () {
    let checkboxes = document.querySelectorAll(".checkbox")
    for (let checkbox of checkboxes) {
        checkbox.addEventListener("click", function () {
            // Do something when the checkbox is clicked
            console.log(this.checked)
        })
    }
}

function settingHover2() {
    document.getElementById("dropAccount").classList.toggle("show")
}

window.onclick = function (ex) {
    if (
        !ex.target.matches(".accountButton") &&
        !ex.target.matches(".fa-caret-down") &&
        !ex.target.matches(".fa-circle-user")
    ) {
        let DropAccount = document.getElementById("dropAccount")
        if (DropAccount.classList.contains("show")) {
            DropAccount.classList.remove("show")
        }
    }
}

/* Set the width of the sidebar to 250px and the left margin of the page content to 250px */

function closeNav() {
    const sideBarWidth = document.getElementById("mySidebar").style.width

    if (sideBarWidth == "0px") {
        if (window.innerWidth <= 768) {
            document.getElementById("mySidebar").style.width = "160px"
            document.getElementById("mainContent").style.marginLeft = "150px"
        } else if (window.innerWidth > 768 && window.innerWidth < 1024) {
            document.getElementById("mySidebar").style.width = "150px"
            document.getElementById("mainContent").style.marginLeft = "150px"
        } else {
            document.getElementById("mySidebar").style.width = "200px"
            document.getElementById("mainContent").style.marginLeft = "200px"
        }
    } else if (sideBarWidth !== "0px") {
        document.getElementById("mySidebar").style.width = "0px"
        document.getElementById("mainContent").style.marginLeft = "0px"
    }
}
