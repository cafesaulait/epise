<h1>Administration EPISE</h1>

<?php if (!empty($msg)): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?><form method="post"><label>Email</label><input type="email" name="log" required><label>Mot de passe</label><input type="password" name="pass" required><button type="submit" name="valide">Connexion</button></form>