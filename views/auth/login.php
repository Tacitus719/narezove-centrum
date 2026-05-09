<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prihlásenie | PROMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; margin: auto; padding: 20px; }
    </style>
</head>
<body>

<div class="login-card card shadow-sm">
    <div class="card-body">
        <h3 class="text-center mb-4">Prihlásenie</h3>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php?page=authenticate" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">E-mailová adresa</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="napr. jan.novak@proma.sk">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Heslo</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Prihlásiť sa</button>
        </form>
    </div>
</div>

</body>
</html>