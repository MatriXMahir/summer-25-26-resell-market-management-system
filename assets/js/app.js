
function clearFieldErrors(form) {
    $(form).find(".field-error").remove();
}

function showFieldError(field, message) {
    $('<div class="field-error"></div>').text(message).insertAfter(field);
}

function validateForm(form, rules) {
    clearFieldErrors(form);
    var valid = true;

    for (var fieldName in rules) {
        if (!rules.hasOwnProperty(fieldName)) continue;
        var field = form.elements[fieldName];
        if (!field) continue;
        var value = String($(field).val()).trim();

        for (var i = 0; i < rules[fieldName].length; i++) {
            var rule = rules[fieldName][i];
            var fails = false;

            if (rule.type === "required" && value === "") fails = true;
            if (rule.type === "email" && value !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) fails = true;
            if (rule.type === "min" && value.length > 0 && value.length < rule.value) fails = true;
            if (rule.type === "number" && value !== "" && isNaN(Number(value))) fails = true;
            if (rule.type === "match" && value !== String($(form.elements[rule.value]).val()).trim()) fails = true;

            if (fails) {
                showFieldError(field, rule.message);
                valid = false;
                break;
            }
        }
    }
    return valid;
}

function escJs(str) {
    return $("<div>").text(str === null || str === undefined ? "" : String(str)).html();
}

function setupAutocomplete(inputId, listId, formId) {
    var $input = $("#" + inputId);
    var $list  = $("#" + listId);
    var $form  = $("#" + formId);
    if ($input.length === 0 || $list.length === 0) return;

    var timer = null;

    $input.on("input", function () {
        clearTimeout(timer);
        var q = String($input.val()).trim();

        if (q.length < 1) {
            $list.hide().empty();
            return;
        }

        timer = setTimeout(function () {
            $.getJSON("index.php", { page: "ajax", action: "autocomplete_products", q: q })
                .done(function (names) {
                    $list.empty();
                    if (!names || names.length === 0) {
                        $list.hide();
                        return;
                    }
                    $.each(names, function (i, name) {
                        $("<li></li>").text(name).on("click", function () {
                            $input.val(name);
                            $list.hide();
                            if ($form.length) $form.trigger("submit");
                        }).appendTo($list);
                    });
                    $list.show();
                })
                .fail(function () { $list.hide(); });
        }, 200);
    });

    $(document).on("click", function (e) {
        if (e.target !== $input.get(0)) $list.hide();
    });
}

function setupLiveSearch(inputId, tbodyId, ajaxAction, renderRow, colspan) {
    var $input = $("#" + inputId);
    var $tbody = $("#" + tbodyId);
    if ($input.length === 0 || $tbody.length === 0) return;

    var timer = null;

    $input.on("input", function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var q = String($input.val()).trim();
            $.getJSON("index.php", { page: "ajax", action: ajaxAction, q: q })
                .done(function (rows) {
                    $tbody.empty();
                    if (!rows || rows.length === 0) {
                        $("<tr></tr>").append(
                            $("<td></td>").attr("colspan", colspan).text("No matching results.")
                        ).appendTo($tbody);
                        return;
                    }
                    $.each(rows, function (i, row) {
                        $tbody.append(renderRow(row));
                    });
                })
                .fail(function () { /* leave the table as-is on network error */ });
        }, 250);
    });
}

$(function () {

    var $qtyInput = $("#newQty");
    var $indicator = $("#stockIndicator");
    if ($qtyInput.length && $indicator.length) {
        var updateIndicator = function () {
            var val = parseInt($qtyInput.val(), 10);
            $indicator.text(
                isNaN(val) || val < 0
                    ? "Enter a quantity"
                    : val === 0
                        ? "Out of stock"
                        : (val < 5 ? "Low stock (" + val + " left)" : "Healthy stock (" + val + ")")
            );
        };
        $qtyInput.on("input", updateIndicator);
        updateIndicator();
    }

    $(".date-header").on("click", function () {
        $("#" + $(this).data("body")).toggleClass("open");
    });

    $(".clickable-row").on("click", function (e) {
        if ($(e.target).closest("a, button, form").length) return;
        $("#" + $(this).data("detail")).toggleClass("open");
    });
});
