<div class="backoffice-login">

    <h1>Administration EPISE</h1>

    <p>
        Connectez-vous pour accéder au backoffice.
    </p>

    <?php if (!empty($msg)): ?>

        <div class="message-erreur">
            <?= htmlspecialchars($msg) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <label for="log">
            Adresse e-mail
        </label>

        <input
            type="email"
            id="log"
            name="log"
            required
            autofocus>

        <label for="pass">
            Mot de passe
        </label>

        <input
            type="password"
            id="pass"
            name="pass"
            required>

        <button
            type="submit"
            name="valide"
            class="button primary">
            Se connecter
        </button>

    </form>

</div>