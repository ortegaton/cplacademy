/* The following function creates a new input field and then calls datePickerController.create();
   to dynamically create a new datePicker widgit for it */
function newline() {
        var total = document.getElementById("newline-wrapper").getElementsByTagName("table").length;
        total++;

        // Clone the first div in the series
        var tbl = document.getElementById("newline-wrapper").getElementsByTagName("table")[0].cloneNode(true);

        // DOM inject the wrapper div
        document.getElementById("newline-wrapper").appendChild(tbl);

        var buts = tbl.getElementsByTagName("a");
        if(buts.length) {
                buts[0].parentNode.removeChild(buts[0]);
                buts = null;
        }

        // Reset the cloned label's "for" attributes
        var labels = tbl.getElementsByTagName('label');

        for(var i = 0, lbl; lbl = labels[i]; i++) {
                // Set the new labels "for" attribute
                if(lbl["htmlFor"]) {
                        lbl["htmlFor"] = lbl["htmlFor"].replace(/[0-9]+/g, total);
                } else if(lbl.getAttribute("for")) {
                        lbl.setAttribute("for", lbl.getAttribute("for").replace(/[0-9]+/, total));
                }
        }

        // Reset the input's name and id attributes
        var inputs = tbl.getElementsByTagName('input');
        for(var i = 0, inp; inp = inputs[i]; i++) {
                // Set the new input's id and name attribute
                inp.id = inp.name = inp.id.replace(/[0-9]+/g, total);
                if(inp.type == "text") inp.value = "";
        }

        // Call the create method to create and associate a new date-picker widgit with the new input
        datePickerController.create();

        var dp = datePickerController.datePickers["dp-normal-1"];

        // No more than 5 inputs
        if(total == 5) document.getElementById("newline").style.display = "none";

        // Stop the event
        return false;
}

function createNewLineButton() {
        var nlw = document.getElementById("newline-wrapper");

        var a = document.createElement("a");
        a.href="#";
        a.id = "newline";
        a.title = "Create New Input";
        a.onclick = newline;
        nlw.parentNode.appendChild(a);

        a.appendChild(document.createTextNode("+"));
        a = null;
}
