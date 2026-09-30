<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistem Informasi Pelanggaran Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>
<body>
<div class="p-3 mb-2 bg-primary-subtle text-primary-emphasis min-vh-100 d-flex align-items-center justify-content-center">

<div class="card">
  <div class="card-body">


<div class="container text-center">
    <h1>Login</h1>

    <form action="proses_login.php" method="POST">

        <label>Email</label><br>
        <input type="email" name="email" required>

        <br><br>

        <label>Password</label><br>
        <input type="password" name="password" required>

        <br><br>

        <button type="submit" class="btn btn-outline-primary">Login</button>

    </form>

    </div>
 </div>
</div>
    
</body>
</html>
