M.auth_zilink_guardian = {

    init : function(Y, sesskey, matched, domain) {
        this.Y = Y;

        this.sesskey = sesskey;

        this.uri = domain;
        this.xhr = null;
        this.year = null;
        this.house = null;
        this.registration = null;
        this.response = null
        this.branch = null;
        this.matched = matched;

        Y.one('#menuconfig_year').on('change', function(e) {
            e.preventDefault();

            this.getoptions();
            this.branch = 'students';
            this.getupdate('action=students&year=' + this.year + '&house=' + this.house + '&registration=' + this.registration + '&sesskey=' + this.sesskey);

        }, this);

        Y.one('#menuconfig_house').on('change', function(e) {
            e.preventDefault();

            this.getoptions();
            this.branch = 'students';
            this.getupdate('action=students&year=' + this.year + '&house=' + this.house + '&registration=' + this.registration + '&sesskey=' + this.sesskey);

        }, this);

        Y.one('#menuconfig_registration').on('change', function(e) {
            e.preventDefault();

            this.getoptions();
            this.branch = 'students';
            this.getupdate('action=students&year=' + this.year + '&house=' + this.house + '&registration=' + this.registration + '&sesskey=' + this.sesskey);
        }, this);

        Y.one('#select_students').on('click', function(e) {
            e.preventDefault();

            var studentlist = document.getElementById('menuconfig_students');
            var selectedstudentlist = document.getElementById('menuconfig_selected_students');

            for (i = studentlist.length - 1; i >= 0; i--) {
                if (studentlist.options[i].selected) {
                    try {
                        selectedstudentlist.add(studentlist.options[i], null);
                    } catch(ex) {
                        selectedstudentlist.add(studentlist.options[i]);
                    }
                }

            }

        }, this);

        Y.one('#deselect_students').on('click', function(e) {
            e.preventDefault();

            var studentlist = document.getElementById('menuconfig_students');
            var selectedstudentlist = document.getElementById('menuconfig_selected_students');

            for (i = selectedstudentlist.length - 1; i >= 0; i--) {
                if (selectedstudentlist.options[i].selected) {
                    try {
                        studentlist.add(selectedstudentlist.options[i], null);
                    } catch(ex) {
                        studentlist.add(selectedstudentlist.options[i]);
                    }
                }
            }

            var guardianlist = document.getElementById('menuconfig_guardians');

            for (i = guardianlist.length - 1; i >= 0; i--) {
                guardianlist.remove(i);
            }

        }, this);

        Y.one('#menuconfig_selected_students').on('click', function(e) {
            e.preventDefault();

            var selectedstudentlist = document.getElementById('menuconfig_selected_students');
            this.branch = 'guardians';
            this.getupdate('action=guardians&matched=' + this.matched + '&idnumber=' + selectedstudentlist.options[selectedstudentlist.selectedIndex].value + '&sesskey=' + this.sesskey);

        }, this);

        Y.one('#select_guardians').on('click', function(e) {
            e.preventDefault();

            var guardianlist = document.getElementById('menuconfig_guardians');
            var selectedguardianlist = document.getElementById('menuconfig_selected_guardians');

            for (i = guardianlist.length - 1; i >= 0; i--) {
                if (guardianlist.options[i].selected) {
                    try {
                        selectedguardianlist.add(guardianlist.options[i], null);
                    } catch(ex) {
                        selectedguardianlist.add(guardianlist.options[i]);
                    }
                }

            }

        }, this);

        Y.one('#deselect_guardians').on('click', function(e) {
            e.preventDefault();

            var selectedguardianlist = document.getElementById('menuconfig_selected_guardians');

            for (i = selectedguardianlist.length - 1; i >= 0; i--) {
                if (selectedguardianlist.options[i].selected) {
                    selectedguardianlist.remove(i);
                }

            }

        }, this);

        Y.one('#authmenu').on('submit', function(e) {

            var selectedguardianlist = document.getElementById('menuconfig_selected_guardians');

            for (i = selectedguardianlist.length - 1; i >= 0; i--) {
                selectedguardianlist.options[i].selected = true;
            }

        }, this);

    },

    updateguardianlist : function(e) {

        var guardianlist = document.getElementById('menuconfig_guardians');
        var selectedguardianlist = document.getElementById('menuconfig_selected_guardians');

        for (i = guardianlist.length - 1; i >= 0; i--) {
            guardianlist.remove(i);
        }

        var elOptNew;
        response = this.response;
        for (g in response) {

            found = false;
            elOptNew = document.createElement('option');
            elOptNew.text = response[g].name;
            elOptNew.value = response[g].idnumber;

            if (selectedguardianlist.length > 0) {
                for (i = selectedguardianlist.length - 1; i >= 0; i--) {
                    if (selectedguardianlist.options[i].value == response[g].idnumber) {
                        found = true;
                    }
                }
            }
            if (found == false) {
                try {
                    guardianlist.add(elOptNew, null);
                } catch(ex) {
                    guardianlist.add(elOptNew);
                }
            }
        }
    },

    updatestudentlist : function(e) {
        var list = document.getElementById('menuconfig_students');

        for (i = list.length - 1; i >= 0; i--) {
            list.remove(i);
        }

        var selectedstudentlist = document.getElementById('menuconfig_selected_students');
        var elOptNew;
        var found;

        response = this.response;
        for (s in response) {
            found = false;
            elOptNew = document.createElement('option');
            elOptNew.text = response[s].name;
            elOptNew.value = response[s].idnumber;

            for (i = selectedstudentlist.length - 1; i >= 0; i--) {
                if (selectedstudentlist.options[i].value == response[s].idnumber) {
                    found = true;
                }
            }
            if (found == false) {
                try {
                    list.add(elOptNew, null);
                } catch(ex) {
                    list.add(elOptNew);
                }
            }
        }
    },

    getoptions : function(e) {

        var sel;
        var opt;

        sel = document.getElementById('menuconfig_year');
        opt = sel.options[sel.selectedIndex];
        this.year = opt.text;

        sel = document.getElementById('menuconfig_house');
        opt = sel.options[sel.selectedIndex];
        this.house = opt.text;

        sel = document.getElementById('menuconfig_registration');
        opt = sel.options[sel.selectedIndex];
        this.registration = opt.text;
    },

    getupdate : function(options) {
        var Y = this.Y;
        this.response = null;

        var uri = this.uri + '/auth/zilink_guardian/action.php';
        if (this.xhr != null) {
            this.xhr.abort();
        }

        this.xhr = Y.io(uri, {
            data : options,
            context : this,
            on : {
                success : function(id, o) {

                    this.response = Y.JSON.parse(o.responseText);

                    if ("guardians" == this.branch) {
                        this.updateguardianlist();
                    } else if ("students" == this.branch) {
                        this.updatestudentlist();
                    }

                },
                failure : function(id, o) {
                }
            }
        });

    }
}