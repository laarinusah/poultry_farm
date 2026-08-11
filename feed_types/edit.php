<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Get Feed Type ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Existing Feed Type
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        feed_name,
        description,
        unit
    FROM feed_types
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$feed = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$feed) {
    header('Location: index.php');
    exit;
}


$error = '';


/*
|--------------------------------------------------------------------------
| Update Feed Type
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $feedName = trim($_POST['feed_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $unit = trim($_POST['unit'] ?? 'bag');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($feedName === '') {

        $error = 'Please enter the feed name.';

    } elseif (strlen($feedName) > 150) {

        $error = 'Feed name cannot exceed 150 characters.';

    } elseif (strlen($description) > 255) {

        $error = 'Description cannot exceed 255 characters.';

    } elseif ($unit === '') {

        $error = 'Please select a feed unit.';

    } elseif (strlen($unit) > 50) {

        $error = 'Unit cannot exceed 50 characters.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Name
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare("
            SELECT id
            FROM feed_types
            WHERE feed_name = ?
              AND id != ?
            LIMIT 1
        ");

        $check->execute([
            $feedName,
            $id
        ]);

        if ($check->fetch()) {

            $error = 'Another feed type with this name already exists.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Update Database
            |--------------------------------------------------------------------------
            */

            $update = $pdo->prepare("
                UPDATE feed_types
                SET
                    feed_name = ?,
                    description = ?,
                    unit = ?
                WHERE id = ?
            ");

            $update->execute([

                $feedName,

                $description !== ''
                    ? $description
                    : null,

                $unit,

                $id

            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            header('Location: index.php?updated=1');
            exit;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Entered Values When Validation Fails
    |--------------------------------------------------------------------------
    */

    $feed['feed_name'] = $feedName;
    $feed['description'] = $description;
    $feed['unit'] = $unit;
}


$pageTitle = 'Edit Feed Type';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>✏️ Edit Feed Type</h2>

        <p>
            Update the information for this feed type.
        </p>

    </div>


    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Feed Types
        </a>

    </div>

</div>


<?php if ($error): ?>

    <div
        class="alert alert-danger"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ⚠️ <?= e($error) ?>

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <form method="POST">


        <!-- Feed Name -->

        <div class="form-group">

            <label for="feed_name">
                🌾 Feed Name
            </label>

            <input
                type="text"
                id="feed_name"
                name="feed_name"
                maxlength="150"
                value="<?= e($feed['feed_name']) ?>"
                placeholder="e.g. Layer Mash"
                required
            >

        </div>


        <!-- Description -->

        <div class="form-group">

            <label for="description">
                📝 Description
            </label>

            <textarea
                id="description"
                name="description"
                maxlength="255"
                rows="4"
                placeholder="Enter a short description"
            ><?= e($feed['description'] ?? '') ?></textarea>

        </div>


        <!-- Unit -->

        <div class="form-group">

            <label for="unit">
                📦 Unit
            </label>

            <select
                id="unit"
                name="unit"
                required
            >

                <option
                    value="bag"
                    <?= $feed['unit'] === 'bag'
                        ? 'selected'
                        : '' ?>
                >
                    Bag
                </option>

                <option
                    value="kg"
                    <?= $feed['unit'] === 'kg'
                        ? 'selected'
                        : '' ?>
                >
                    Kilogram (kg)
                </option>

                <option
                    value="ton"
                    <?= $feed['unit'] === 'ton'
                        ? 'selected'
                        : '' ?>
                >
                    Ton
                </option>

                <option
                    value="other"
                    <?= $feed['unit'] === 'other'
                        ? 'selected'
                        : '' ?>
                >
                    Other
                </option>

            </select>

        </div>


        <!-- Buttons -->

        <div
            style="
                display:flex;
                gap:10px;
                flex-wrap:wrap;
                margin-top:20px;
            "
        >

            <button
                type="submit"
                class="btn btn-primary"
            >
                💾 Update Feed Type
            </button>


            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


<style>

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
}

@media (max-width: 768px) {

    .dashboard-card {
        padding: 15px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        font-size: 16px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>