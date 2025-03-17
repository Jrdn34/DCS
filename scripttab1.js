$(document).ready(function () {
    $('#filterFormTab1').on('submit', function (e) {
        e.preventDefault();
        var grandClientID = $('#grandClientTab1').val();
        $.ajax({
            url: 'tab1.php',
            type: 'GET',
            data: { grandClient: grandClientID },
            success: function (response) {
                var result = JSON.parse(response);
                var table = '<table class="table table-striped">';
                table += '<thead><tr><th>Nom de l\'application</th><th>Prix total</th><th>Nom du grand client</th></tr></thead>';
                table += '<tbody>';
                result.forEach(function (row) {
                    table += '<tr>';
                    table += '<td>' + row.nomAppli + '</td>';
                    table += '<td>' + row.totalPrix + '€</td>';
                    table += '<td>' + row.nomGrandClient + '</td>';
                    table += '</tr>';
                });
                table += '</tbody></table>';
                $('#resultTable').html(table);
            }
        });
    });
});