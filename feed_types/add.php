<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$error = '';

/*
|--------------------------------------------------------------------------
| Handle Form Submission
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

        $error = 'Please enter the feed unit.';

    } elseif (strlen($unit) > 50) {

        $error = 'Unit cannot exceed 50 characters.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Feed Type
        |--------------------------------------------------------------------------
        */

        $check = $pdo->prepare("
            SELECT id
            FROM feed_types
            WHERE feed_name = :feed_name
            LIMIT 1
        ");

        $check->execute([
            ':feed_name' => $feedName
        ]);

        if ($check->fetch()) {

            $error = 'A feed type with this name already exists.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert Feed Type
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO feed_types
                (
                    feed_name,
                    description,
                    unit
                )
                VALUES
                (
                    :feed_name,
                    :description,
                    :unit
                )
            ");

            $stmt->execute([

                ':feed_name' => $feedName,

                ':description' =>
                    $description !== ''
                        ? $description
                        : null,

                ':unit' => $unit

            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            header('Location: index.php?added=1');
            exit;
        }
    }
}


$pageTitle = 'Add Feed Type';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🌾 Add Feed Type</h2>

        <p>
            Add a new type of feed used on the farm.
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

    <form
        method="POST"
        action=""
    >


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
                value="<?= e(
                    $_POST['feed_name'] ?? ''
                ) ?>"
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
                placeholder="Enter a short description of this feed"
            ><?= e(
                $_POST['description'] ?? ''
            ) ?></textarea>

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

                <?php

                $selectedUnit =
                    $_POST['unit']
                    ?? 'bag';

                ?>

                <option
                    value="bag"
                    <?= $selectedUnit === 'bag'
                        ? 'selected'
                        : '' ?>
                >
                    Bag
                </option>

                <option
                    value="kg"
                    <?= $selectedUnit === 'kg'
                        ? 'selected'
                        : '' ?>
                >
                    Kilogram (kg)
                </option>

                <option
                    value="ton"
                    <?= $selectedUnit === 'ton'
                        ? 'selected'
                        : '' ?>
                >
                    Ton
                </option>

                <option
                    value="other"
                    <?= $selectedUnit === 'other'
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
                💾 Save Feed Type
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