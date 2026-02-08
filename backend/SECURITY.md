# Sécurité – LudoPlanet Backend & Frontend

## Backend (Symfony)

### Injections SQL

- **OK** : Doctrine / DQL avec `$repository->find()`, `findBy()`, paramètres liés. Pas de requêtes SQL brutes concaténées.

### Authentification

- **OK** : JWT (Lexik) avec cookie `AUTH_TOKEN` (httpOnly, SameSite=Lax). Le front envoie le cookie via `credentials: 'include'`.
- **OK** : Rate limiting sur `/api/login` et `/api/register` (10 requêtes / minute / IP) via `RateLimitLoginSubscriber`.

### Contrôle d’accès

- **OK** : `access_control` dans `security.yaml` : `/api/admin` et `/api/orders` réservés à `ROLE_ADMIN`.
- **OK** : Commandes utilisateur : `myOrders` filtre par `['user' => $user]` ; `myOrderShow` vérifie `$order->getUser()?->getId() === $user->getId()`. Attribut `#[IsGranted('ROLE_ADMIN')]` sur les actions admin des commandes.

### XSS

- Backend : pas de rendu HTML direct ; API JSON. Twig échappe par défaut si utilisé.
- Frontend Svelte : éviter `{@html}` avec des données non contrôlées. Aucun `{@html}` utilisé actuellement.

### Mots de passe

- **OK** : `security.yaml` → `password_hashers: ... "auto"` (Argon2 / bcrypt selon l’environnement).

### CORS

- **OK** : Nelmio CORS (`nelmio/cors-bundle`) n’autorise que les origines définies (liste par défaut en dev ; en prod via `CORS_ALLOWED_ORIGINS`).
- **Prod** : définir `CORS_ALLOWED_ORIGINS` dans `.env` (liste complète des origines autorisées, séparées par des virgules, ex. `https://mondomaine.com` ou les mêmes que en dev).

### Logs

- **OK** : Monolog activé. Les échecs de connexion sont loggés (IP, email si présent, message) via `LoginFailureLogSubscriber` (niveau `warning`).

---

## Frontend (SvelteKit)

### Exposition du token

- **OK** : Le JWT est stocké dans un **cookie httpOnly** (`AUTH_TOKEN`) posé par le backend au login. Le front n’a pas accès au token en JS ; il envoie le cookie avec `credentials: 'include'`. Pas de localStorage pour l’auth.

### CSRF

- Stripe gère la sécurité de son flux (paiement).
- API stateless (JWT) : pas de cookie de session Symfony côté front ; le cookie JWT est envoyé par le navigateur. Pour des formulaires personnalisés qui modifient des données sensibles, un token CSRF Symfony peut être ajouté si besoin (non en place pour l’instant sur l’API JSON).

### HTTPS

- **Prod** : forcer HTTPS (config hébergeur, ex. Hostinger).

---

## Variables d’environnement (prod)

- `CORS_ALLOWED_ORIGINS` : origines autorisées pour CORS (ex. `https://ton-front.com`).
- `APP_ENV=prod`, `APP_DEBUG=0`.
- Secrets JWT, Stripe, base de données : à configurer en production.

---

## Vérifier que tout fonctionne

### Tests automatisés (backend)

```bash
cd backend
php bin/phpunit
```

- **Rate limit** : `RateLimitLoginTest` envoie plusieurs POST sur `/api/login` et vérifie qu’une réponse 429 « Trop de tentatives » est bien renvoyée.
- **Accès commandes** : `OrderControllerTest` vérifie que `my-orders` exige l’auth et que seules les commandes de l’utilisateur connecté sont retournées ; les routes admin `/api/orders` exigent le rôle admin.
- **Admin** : les tests dans `Admin*ControllerTest` vérifient que les routes admin renvoient 401 sans token.

### Vérifications manuelles rapides

1. **Rate limit** : depuis le navigateur ou avec `curl`, envoie 11+ requêtes POST vers `http://localhost:8000/api/login` (body JSON `{"email":"x@x.com","password":"x"}`). À partir de la 11ᵉ, la réponse doit être **429** avec un message du type « Trop de tentatives ».
2. **CORS** : ouvre la console du navigateur sur ton front (ex. `http://localhost:5173`), lance une requête vers l’API. En dev, pas d’erreur CORS. En prod, sans `CORS_ALLOWED_ORIGINS` correct, les requêtes depuis ton domaine peuvent être bloquées.
3. **Logs d’échec de login** : après quelques tentatives de connexion volontairement ratées, vérifier que `var/log/dev.log` (ou le log configuré) contient des lignes « Échec de connexion » avec IP / email.
4. **Cookie JWT** : après un login réussi, dans les DevTools → Application → Cookies, le cookie `AUTH_TOKEN` doit être présent et avoir **HttpOnly** coché.
