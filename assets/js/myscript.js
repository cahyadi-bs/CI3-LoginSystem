//tombol-hapus
$('.delete-button').on('click', function(e){
    e.preventDefault(); // mematikkan href
    const href = $(this).attr('href');
    Swal.fire({
        title: 'Are you sure?',
        text: "Menu will be deleted",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33' ,
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'DELETE!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.location.href = href;
        }
    })
});

$('.custom-file-input').on('change', function(){
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});



// fitur checkbox di change access
$('.form-check-input').on('click',function(){
    const menuId = $(this).data('menu');
    const roleId = $(this).data('role');

    $.ajax({
        url: base_url + "admin/changeAccess",
        type: 'post',
        data : {
            menuId: menuId,
            roleId: roleId
        },
        success: function (){
            document.location.href = base_url + "admin/roleAccess/" + roleId;
        }
    });
});