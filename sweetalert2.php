<?php
if (isset($_SESSION["SUCCESS_LOGIN"])) { ?>
  <script>
    Swal.fire({
      position: 'top-end',
      toast: true,
      icon: '<?php echo $_SESSION["SUCCESS_LOGIN"][0]; ?>', //error , success , info
      title: '<?php echo $_SESSION["SUCCESS_LOGIN"][1]; ?>',
      showConfirmButton: false,
      timer: 3500,
      background: '#000000', // เพิ่มพื้นหลังสีดำ
      color: '#ffffff' // สีข้อความเป็นสีขาวเพื่อให้อ่านได้ง่ายขึ้น
    })
  </script>
<?php unset($_SESSION["SUCCESS_LOGIN"]);
} ?>
<?php
if (isset($_SESSION["RESETPASS"])) { ?>
  <script>
    Swal.fire({
      // position: 'top-end',
      // toast: true,
      icon: '<?php echo $_SESSION["RESETPASS"][0]; ?>', //error , success , info
      title: '<?php echo $_SESSION["RESETPASS"][1]; ?>',
      showConfirmButton: false,
      // timer: 3500,
      background: '#000000', // เพิ่มพื้นหลังสีดำ
      color: '#ffffff' // สีข้อความเป็นสีขาวเพื่อให้อ่านได้ง่ายขึ้น
    })
  </script>
<?php unset($_SESSION["RESETPASS"]);
} ?>

