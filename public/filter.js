$(function() {
    let type = "and";
    let players = [];
    let teams = [];
    let positions = [];

    function removeFromList(list, item) {
        return $.grep(list, function (value) {
            return value !== item;
        });
    }
    function filterList() {
        let row = $("tr.match");
        row.removeClass("d-none");

        if (players.length) {
            let check = [];

            $.each(players, function(index, playerName) {
                if (teams.length === 0 && positions.length === 0) {
                    check.push(playerName);
                } else {
                    if (teams.length) {
                        $.each(teams, function(index, team) {
                            check.push(team + playerName);
                        });
                    }
                    if (positions.length) {
                        $.each(positions, function(index, position) {
                            check.push(position + playerName);
                        });
                    }
                }
            });

            row.each(function () {
                if (type === "and") {
                    let currentRow = $(this);
                    $.each(check, function(index, element) {
                        if (!currentRow.hasClass(element)) {
                            currentRow.addClass("d-none");
                            return false;
                        }
                    });
                } else {
                    let found = false;
                    let currentRow = $(this);
                    $.each(check, function(index, element) {
                        if (currentRow.hasClass(element)) {
                            found = true;
                            return false;
                        }
                    });
                    if (!found) {
                        $(this).addClass("d-none");
                    }
                }
            });
        }
    }
    function filterAdd(filter) {
        if (filter.hasClass("type")) {
            $(".filter.type").removeClass("badge-success").addClass("badge-light");
            type = filter.data("filter");
            filter.removeClass("badge-light").addClass("badge-success");
        } else if (filter.hasClass("player")) {
            players.push(filter.data("filter"));
        } else if (filter.hasClass("team")) {
            teams.push(filter.data("filter"));
        } else if (filter.hasClass("position")) {
            positions.push(filter.data("filter"));
        }
        filterList();
    }
    function filterRemove(filter) {
        if (filter.hasClass("type")) {
            filter.removeClass("badge-light").addClass("badge-success");
            return;
        } else if (filter.hasClass("player")) {
            players = removeFromList(players, filter.data("filter"));
        } else if (filter.hasClass("team")) {
            teams = removeFromList(teams, filter.data("filter"));
        } else if (filter.hasClass("position")) {
            positions = removeFromList(positions, filter.data("filter"));
        }
        filterList();
    }

    $(".filter").click(function(e) {
        e.preventDefault();
        if ($(this).hasClass("badge-light")) {
            $(this).removeClass("badge-light").addClass("badge-success");
            filterAdd($(this));
        } else {
            $(this).removeClass("badge-success").addClass("badge-light");
            filterRemove($(this));
        }
    });
});
