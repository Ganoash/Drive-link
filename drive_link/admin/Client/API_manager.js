jQuery(function($) {
    let container = $(".api_container")

/**
 *  request enumeration as follows:
 *  1 => user
 *  2 => groups
 *  3=> members
 *
 **/
    /**
     * introductions of global variables userList and groupList, final containers for retrieved model data
     */
    let userList , groupList;
    get_request(generate_model, 3)


    let current_viewdata;
    /**
     * function for handling the loading of userlist and grouplist, and allowing functions in clientside
     */
    function generate_model(response) {
        json_to_membership(response);
        load_nav_onclicks();
        $("#wait_for_load").empty();
        load("user", userList.data);

        //generate google user datalist
        create_google_datalist();
    }

    function create_google_datalist() {
        let html = "<datalist id='google_users'>"
        userList.data.forEach(function (item) {
            html += "<option value='" + item.email + "'>" + item.voornaam + " " + item.achternaam + "</option>";
        });
        html += "</datalist>";

        $("#google_user").replaceWith(html);
    }
    /**
     * JSON parsers for data containers
     */

    function json_to_user_array(response) {
        let user_array = [];
        response.forEach(function (item, index) {
            let voornaam = item.name.givenName;
            let achternaam = item.name.familyName;
            let gebruikersnaam= item.primaryEmail.split("@")[0];
            let email = item.primaryEmail;
            let recovery_email = item.recoveryEmail;
            user_array.push(new User(voornaam, achternaam, gebruikersnaam, email, recovery_email))


        });
        return user_array;
    }

    function json_to_group_array(response) {
        let group_array = [];
        response.forEach(function (item, index) {
            let email = item.email;
            let name = item.name;
            let description = item.description;

            group_array.push(new Group(name, email, description));
        })
        return group_array;
    }

    function json_to_membership(response) {
        let user_list = new UserList(json_to_user_array(response[0]));
        let group_list = new GroupList(json_to_group_array(response[1]));
        let membership_array = response[2];

        membership_array.forEach(function (item, index) {
            let user = user_list.findEmail(item.user.primaryEmail);
            let group = group_list.findEmail(item.group.email);
            addMember(user, group);
        });

        userList = user_list;
        groupList = group_list;
    }

    /**
     * function for making requests to the serverside REST_API
     * @param callback function handling the response
     * @param id which api function should be called (enum meaning above)
     */
    function get_request(callback, id) {
        $.ajax({
            type: 'GET',
            url: '/wp-json/yeti_drive_link/v1/directory/' + id,
            contentType: "application/json",
            beforeSend(jqXHR) {
                jqXHR.setRequestHeader( 'X-WP-Nonce', ajax_var.nonce );
            },
            error: function (jqXhr, textStatus, errorMessage) {
                console.log(errorMessage);
            },
            // contentType: 'application/json',
        }).done(callback);
    }

    function post_user_request(voornaam, achternaam, gebruikersnaam, email, callback) {
        console.log("creating post request");
        $.ajax({
            type: 'POST',
            url: '/wp-json/yeti_drive_link/v1/directory/4',
            beforeSend(jqXHR) {
                jqXHR.setRequestHeader( 'X-WP-Nonce', ajax_var.nonce );
            },
            data: {
                voornaam: voornaam,
                achternaam: achternaam,
                gebruikersnaam: gebruikersnaam,
                email: email
            },
            error: function (jqXhr, textStatus, errorMessage) {
                $("#member_error").replaceWith("<p id='member_error'>" + errorMessage +"</p>");
                console.log(textStatus);
                console.log(jqXhr);
            }
        }).done(callback);
    }

    function post_add_member_request(user_email, group_email, callback) {
        console.log("creating post request");
        $.ajax({
            type: 'POST',
            url: '/wp-json/yeti_drive_link/v1/directory/5',
            beforeSend(jqXHR) {
                jqXHR.setRequestHeader( 'X-WP-Nonce', ajax_var.nonce );
            },
            data: {
                user_email: user_email,
                group_email: group_email
            },
            error: function (jqXhr, textStatus, errorMessage) {
                $("#member_error").replaceWith("<p id='member_error'>" + errorMessage +"</p>");
                console.log(textStatus);
                console.log(jqXhr);
            }
        }).done(callback);
    }

    function post_remove_member_request(user_email, group_email, callback) {
        console.log("creating post request");
        $.ajax({
            type: 'POST',
            url: '/wp-json/yeti_drive_link/v1/directory/6',
            beforeSend(jqXHR) {
                jqXHR.setRequestHeader( 'X-WP-Nonce', ajax_var.nonce );
            },
            data: {
                user_email: user_email,
                group_email: group_email
            },
            error: function (jqXhr, textStatus, errorMessage) {
                $("#member_error").replaceWith("<p id='member_error'>" + errorMessage +"</p>");
                console.log(textStatus);
                console.log(jqXhr);
            }
        }).done(callback);
    }


    /**
     * CLIENT SIDE data containers below
     */
    function User(voornaam, achternaam, gebruikersnaam, email, recoveryEmail) {
        if(recoveryEmail === null) recoveryEmail = "";
        let user = {
            voornaam: voornaam,
            achternaam: achternaam,
            gebruikersnaam: gebruikersnaam,
            email: email,
            recoveryEmail: recoveryEmail
        };

        user.group = [];

        user.to_html = function (with_column = "") {
            return "<div id='user_" + this.email + "' class='item'>"
                + "<div class='within'><p>" + this.voornaam + "</p></div>"
                + "<div class='within'><p>" + this.achternaam + "</p></div>"
                + "<div class='within'><p>" + this.email + "</p></div>"
                + with_column
                + "</div>";
        }

        user.withGroup = function (group) {
            this.group.push(group);
        }

        user.removeGroup = function (to_remove) {
            this.group.splice(this.group.indexOf(to_remove), 1);
        }

        user.find = function (needle) {
            needle = needle.toLowerCase();
            let ret = false;
            ret |= this.voornaam.toLowerCase().includes(needle);
            ret |= this.achternaam.toLowerCase().includes(needle);
            ret |= this.gebruikersnaam.toLowerCase().includes(needle);
            ret |= this.email.toLowerCase().includes(needle);
            ret |= this.recoveryEmail.toLowerCase().includes(needle);
            return ret;
        }
        return user;
    }

    function Group(naam, email, beschrijving) {
        if(beschrijving === undefined) beschrijving = "";

        let group = {
            naam: naam,
            email: email,
            beschrijving: beschrijving
        }

        group.users = [];

        group.to_html = function () {
            return "<div id='group_" + this.email + "' class='item'>"
                + "<div class='within'><p>" + this.naam + "</p></div>"
                + "<div class='within'><p>" + this.email + "</p></div>"
                + "<div class='within'><p>" + this.beschrijving + "</p></div>"
                + "</div>";
        }

        group.find = function (needle) {
            needle = needle.toLowerCase();
            let ret = false;
            ret |= this.naam.toLowerCase().includes(needle);
            ret |= this.email.toLowerCase().includes(needle);
            ret |= this.beschrijving.toLowerCase().includes(needle);
            return ret;
        }

        group.withUser = function (user) {
            this.users.push(user);
        }

        group.removeUser = function (to_remove) {
            this.users.splice(this.users.indexOf(to_remove), 1);
        }

        return group;
    }

    function addMember(user, group) {
        user.withGroup(group);
        group.withUser(user);
    }

    function UserList(list) {
        let listWrapper ={data: list};
        listWrapper.findEmail = function (email) {
            let ret;
            this.data.forEach(function(item, index) {
                if(item.email.trim() === email.trim()) {
                    ret = item;
                }
            });

            return ret;
        }
        return listWrapper;
    }

    function GroupList(list) {
        let listWrapper ={data: list};
        listWrapper.findEmail = function (email) {
            let ret;
            this.data.forEach(function(item) {
                if(item.email.trim() === email.trim()) {
                    ret = item;
                }
            });

            return ret;
        }
        return listWrapper;
    }


    /**
     * button onclick listeners
     */


    /**
     * specific plugin components
     */

    function add_search_button() {
        $("#api_zoek").show();
    }

    function remove_search_button() {
        $("#api_zoek").hide();
    }

    function show_back_footer() {
        $("#back_footer").show();
    }

    function hide_back_footer() {
        $("#back_footer").hide();
    }

    function user_onclick() {
        container.empty();
        let header = "<div id='user_header' class='item'>"
            + "<div class='within'><p>voornaam</p></div>"
            + "<div class='within'><p>achternaam</p></div>"
            + "<div class='within'><p>emailadres</p></div>"
            + "</div>";
        container.append(header);

        load("user", userList.data);
    }

    function member_onclick() {
        container.empty();

        load("detailedGroup", groupList.data);
    }

    function group_onclick() {
        container.empty();
        let header = "<div id='user_header' class='item'>"
            + "<div class='within'><p>groepnaam</p></div>"
            + "<div class='within'><p>groep email</p></div>"
            + "<div class='within'><p>beschrijving</p></div>"
            + "</div>";
        container.append(header);

        load("group", groupList.data);
    }

    function item_onclick(event) {
        event.stopPropagation();
        event.stopImmediatePropagation();

        let email = this.id.split("_")[1];
        let type = this.id.split("_")[0];
        let object = [];
        let view = "";

        container.empty();
        switch (type) {
            case "user": object.push(userList.findEmail(email));
                view = "detailedUser";
                break;
            case "group": object.push(groupList.findEmail(email));

                view = "addMember";
                break;
        }

        load(view, object);
    }

    function filter_data(event) {
        current_viewdata.filter = event.target.value;
        current_viewdata.load();

    }

    function show_add_user() {
        load("addUser", null);
    }

    function search_user_data() {
        let value = $("#user_search").val();
        let ret;
        user_data.forEach(function (item) {
            if(item.display_name.trim().toLowerCase() === value.trim().toLowerCase()) {
                ret = item;
            }
        });

        if(ret === undefined) {
            $("#error_return").replaceWith("<p id='error_return'>gebruiker niet gevonden</p>");
        }

        else {
            $("#first_name_info").replaceWith("<p id='first_name_info'>" + ret.voornaam + "</p>");
            $("#last_name_info").replaceWith("<p id='last_name_info'>" + ret.achternaam + "</p>");
            $("#email_info").replaceWith("<p id='email_info'>" + ret.user_email + "</p>");
            $("#username_info").replaceWith("<p id='username_info'>" + ret.user_login + "@yeti.alpenclub.nl" + "</p>");
        }
    }

    function add_user_to_database() {
        let voornaam = $("#first_name_info").text();
        let achternaam = $("#last_name_info").text();
        let email = $("#email_info").text();
        let username = $("#username_info").text().split("@")[0];

        console.log("initializing post request")
        console.log(voornaam)
        console.log(achternaam)
        console.log(email)
        console.log(username)

        post_user_request(voornaam, achternaam, username, email, function (request) {
            $("#error_return").replaceWith("<p id='error_return'>gebruiker succesvol toegevoegd</p>");
        })
    }

    function back_onclick() {
        current_viewdata.back();
    }


    function load_nav_onclicks() {
        let search = $("#api_zoek");
        search.val("");

        $("#api_users").on("click", user_onclick);
        $("#api_members").on("click", member_onclick);
        $("#api_groups").on("click", group_onclick);
        $("#api_add_users").on("click", show_add_user);
        $("#back_footer_link").on("click", back_onclick);
        search.on("change", filter_data);
    }

    /**
     * top level view logic
     *
     */




    function load(view, curr_array, transition=true) {
        if(current_viewdata === undefined) {
            current_viewdata = new ViewData(view, curr_array);
        }
        if(transition) current_viewdata.transition(view, curr_array);
        show(view, curr_array);
        after_load();
    }

    function ViewData(view, curr_array, lastview) {
        let ob = {}
        ob.viewType = view;
        ob.data = curr_array;
        ob.filter = undefined;
        ob.lastview = lastview;

        ob.execute_filter = function () {
            let filter = this.filter;

            return this.data.filter(function (item) {
                return item.find(filter);
            });
        }

        ob.load = function (execute_filter = true) {
            let data = this.data;
            if(execute_filter) data=this.execute_filter(this.data);
            load(this.viewType, data, false);
        }

        ob.transition = function (view, curr_array) {
            if(this.viewType === "addUser") {
                $(".api_add_user").hide();
                $("#show_user").off("click");
                $("#submit_user").off("click");
                container.show();
                show_back_footer();
            }

            if(this.viewType === "addMember") {
                $("#add_member").off("click");
                $(".remove_member").off("click");
                $("#show_member").off("click");
            }

            ob.lastview = new ViewData(this.viewType, this.data, this.lastview);

            if(!(curr_array === null)) {
                this.data = curr_array;
            }
            this.viewType = view;
            this.filter = undefined;
        }

        ob.back = function () {
            if(this.lastview === undefined) return;
            this.data = this.lastview.data;
            this.viewType = this.lastview.viewType;
            this.lastview = this.lastview.lastview;
            this.load(false);
        }

        return ob;
    }

    function after_load() {
        let item = $(".item");
        item.off("click")
        console.log(current_viewdata.viewType);
        if(current_viewdata.viewType !== "addMember") item.on("click", item_onclick);
        window.scrollTo(0,0)
    }

    /**
     * functions for different view types
     */

    /**
     * userview shows just user_data
     * @param view string to define type of view
     * @param curr_array current list of users or groups
     */

    function show(view, curr_array) {
        container.empty();
        switch (view) {
            case "user": userView(curr_array); break;
            case "group": groupView(curr_array); break;
            case "detailedUser": detailedUserView(curr_array); break;
            case "detailedGroup": detailedGroupView(curr_array); break;
            case "addUser": addUserView(); break;
            case "addMember": addMemberView(curr_array); break;
        }
    }

    function userView(curr_array) {
        let html = "";
        curr_array.forEach(function (item, index) {
            html += item.to_html();
        });
        container.append(html);
        add_search_button();
    }

    /**
     * detailedGroupView shows user data with group data per user
     * @param curr_array
     */
    function detailedUserView(curr_array) {
        let html ="";
        curr_array.forEach(function (item) {
            html += item.to_html();
            item.group.forEach(function (group) {
                html +=group.to_html();
            });
            html += "<br>";
        });
        container.append(html);
        remove_search_button();
    }

    /**
     * group view shows just group data
     * @param curr_array
     */
    function groupView(curr_array) {
        let html = "";
        curr_array.forEach(function (item) {
            html += item.to_html();
        });
        container.append(html);
        add_search_button();
    }

    /**
     * detailedGroupView shows group data with user data per group
     * @param curr_array
     */
    function detailedGroupView(curr_array) {
        let html ="";
        curr_array.forEach(function (item) {
            html += item.to_html();
            item.users.forEach(function (user) {
                html += user.to_html();
            });
            html += "<div class=\"group_footer\"><a id='add_member_" + item.email + "' class='add_member_link'>nieuwe gebruiker toevoegen</a></div>";
            html += "<br>";
        });
        container.append(html);
        loadDetailedGrouponClick();
        remove_search_button();
    }

    function addUserView() {
        $("#show_user").on("click", search_user_data);
        $("#submit_user").on("click", add_user_to_database);
        container.hide();
        $(".api_add_user").show();
        remove_search_button();
        hide_back_footer();
    }

    function addMemberView(curr_array) {
        let html = "";
        if(curr_array.length > 1) {
            console.log("Called addMemberView wrongly, expected 1 group object got:"  +curr_array.length);
            throw new DOMException("Wrong call of add member view");
        }

        curr_array.forEach( function (item) {
            html += item.to_html();
            item.users.forEach(function (user) {
                html+= user.to_html("<div class='within'><button id='remove_member_" + user.email + "_" + item.email + "' class='delete_member'>verwijder gebruiker uit groep</button></div>");
            });
            html+="<input type='hidden' value='" + item.email + "' id='add_member_group'>"
        });
        html += "<div class='add_member_footer'>" +
            "   <div class='add_member_row'>" +
            "   <div class='info'><input type='text' list='google_users' id='add_member_val'></div>" +
            "        <div class=\"info\">" +
            "            <label for=\"member_first_name_info\">Voornaam</label>" +
            "            <p id=\"member_first_name_info\"></p>" +
            "        </div>" +
            "        <div class=\"info\">" +
            "            <label for=\"member_last_name_info\">Achternaam</label>" +
            "            <p id=\"member_last_name_info\"></p>" +
            "        </div>" +
            "        <div class=\"info\">" +
            "            <label for=\"member_username_info\">Google Email</label>" +
            "            <p id=\"member_username_info\"></p>" +
            "        </div>" +
            "        <div class='info'>" +
            "            <button id='show_member'>laad gebruiker</button>" +
            "        </div>" +
            "   </div>" +
            "   <button id='add_member'>gebruiker toevoegen</button><p id='member_error'></p>" +
            "</div>"
        container.append(html);
        loadMemberOnclicks();
    }

    function loadMemberOnclicks() {
        $("#add_member").on("click", function () {
           let userEmail = $("#add_member_val").val();
           let groupEmail = $("#add_member_group").val();

           post_add_member_request(userEmail, groupEmail, function (response) {
               $("#member_error").replaceWith("<p id='member_error'>gebruiker succesvol toegevoegd</p>");
               let user = userList.findEmail(userEmail);
               let group = groupList.findEmail(groupEmail);
               user.withGroup(group);
               group.withUser(user);

               load("addMember", [group]);
           });

        });

        $("#show_member").on("click", function(event) {
            let userEmail = $("#add_member_val").val();

            let user = userList.findEmail(userEmail);
            let voornaam = $("#member_first_name_info");
            let achternaam = $("#member_last_name_info");
            let email = $("#member_username_info");

            voornaam.empty();
            achternaam.empty();
            email.empty();

            voornaam.append(user.voornaam);
            achternaam.append(user.achternaam);
            email.append(user.email);
        });

        $(".delete_member").on("click", function (event) {
            console.log(this.id);
            let user_email = this.id.split("_")[2];
            let group_email = this.id.split("_")[3];

            post_remove_member_request(user_email, group_email, function (response) {
                $("#member_error").replaceWith("<p id='member_error'>gebruiker succesvol verwijdert</p>");
                let user = userList.findEmail(user_email);
                let group = groupList.findEmail(group_email);
                user.removeGroup(group);
                group.removeUser(user);

                load("addMember", [group]);
            });
        });
    }

    function loadDetailedGrouponClick() {
        $(".add_member_link").on("click", function (event) {
            let group_email = this.id.split("_")[2];
            let group = groupList.findEmail(group_email);

            load("addMember", [group]);
        })
    }

});