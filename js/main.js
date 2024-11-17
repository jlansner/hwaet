"use strict";

if (window.location.protocol === "http:") {
  window.location.href = window.location.href.replace("http:", "https:");
}
var translationsWithHwaet = _.filter(translations, function (row) {
    return row.translation.trim() && row.not_translated !== "1";
  }),
  numberOfTranslations = translationsWithHwaet.length,
  getRandomNumber = function getRandomNumber(currentNum) {
    var num = Math.floor(Math.random() * numberOfTranslations);
    return num === currentNum ? getRandomNumber(currentNum) : num;
  },
  showTranslation = function showTranslation() {
    activeDiv = getRandomNumber(activeDiv);
    $(".textWrapper.active").removeClass("active");
    $(".textWrapper").eq(activeDiv).addClass("active");
    $("#pageWrapper").css({
      "background-color": getRandomColor()
    });
  },
  getRandomColor = function getRandomColor() {
    var red = Math.floor(Math.random() * 255),
      green = Math.floor(Math.random() * 255),
      blue = Math.floor(Math.random() * 255),
      opacity = Math.random() / 2;
    return "rgba( ".concat(red, ", ").concat(green, ", ").concat(blue, ", ").concat(opacity);
  },
  showAll = function showAll() {
    $("body").addClass("showInfo");
    if (!DataTable.isDataTable("#fullListTable")) {
      var datatable = $("#fullListTable").DataTable({
        paging: true,
        pageLength: 25,
        searching: true,
        info: true,
        columnDefs: [{
          target: 2,
          type: "num"
        }, {
          target: 7,
          visible: false
        }],
        order: [2, "asc"],
        layout: {
          topEnd: null
        },
        initComplete: function initComplete() {
          var searchColumns = [0, 1, 5, 6],
            sortColumns = [3, 4];
          this.api().columns().every(function () {
            var column = this,
              header = column.header(),
              title = header.textContent;
            if (searchColumns.includes(column.index())) {
              var input = document.createElement("input");
              input.placeholder = title;
              header.append(input);
              input.addEventListener("keyup", function () {
                if (column.search() !== this.value) {
                  column.search(input.value).draw();
                }
              });
              $(input).click(function (event) {
                event.stopPropagation();
              });
            } else if (sortColumns.includes(column.index())) {
              var select = $("<select><option value=\"\"></option></select>").appendTo($(column.header())).on("change", function () {
                column.search($(this).val(), {
                  exact: true
                }).draw();
              });
              column.data().unique().sort(function (a, b) {
                return _.lowerCase(a).localeCompare(_.lowerCase(b));
              }).each(function (d, j) {
                select.append("<option value=\"".concat(d, "\">").concat(d, "</option>"));
              });
              $(select).click(function (event) {
                event.stopPropagation();
              });
            }
          });
        }
      });
    }
  },
  all = window.location.hash === "#all";
var activeDiv = Math.floor(Math.random() * numberOfTranslations);
$(document).ready(function () {
  showTranslation();
  setInterval(showTranslation, 5000);
  $("body").on("click", "footer div", function () {
    showAll();
  });
  $("body").on("click", ".closeOverlay", function () {
    $("body").removeClass("showInfo");
  });
  if (all) {
    showAll();
  }
});