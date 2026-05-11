<?php

function ensure_simple_grades_schema(PDO $pdo): void
{
    $columns = [
        "semestre_id" => "ALTER TABLE note ADD COLUMN semestre_id INT NULL AFTER module_id",
        "devoir_1" => "ALTER TABLE note ADD COLUMN devoir_1 DECIMAL(4,2) NULL AFTER penalite",
        "devoir_2" => "ALTER TABLE note ADD COLUMN devoir_2 DECIMAL(4,2) NULL AFTER devoir_1",
        "devoir_3" => "ALTER TABLE note ADD COLUMN devoir_3 DECIMAL(4,2) NULL AFTER devoir_2",
        "note_classe" => "ALTER TABLE note ADD COLUMN note_classe DECIMAL(4,2) NULL AFTER devoir_3",
        "note_examen" => "ALTER TABLE note ADD COLUMN note_examen DECIMAL(4,2) NULL AFTER note_classe",
        "note_finale" => "ALTER TABLE note ADD COLUMN note_finale DECIMAL(4,2) NULL AFTER note_examen",
        "created_by" => "ALTER TABLE note ADD COLUMN created_by VARCHAR(10) NULL AFTER note_finale",
        "updated_by" => "ALTER TABLE note ADD COLUMN updated_by VARCHAR(10) NULL AFTER created_by",
        "updated_at" => "ALTER TABLE note ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER updated_by",
    ];

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'note'
        AND COLUMN_NAME = ?
    ");

    foreach ($columns as $column => $sql) {
        $stmt->execute([$column]);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }
}

function grade_number($value): ?float
{
    if ($value === null || $value === "") {
        return null;
    }

    $number = filter_var(str_replace(",", ".", (string) $value), FILTER_VALIDATE_FLOAT);

    if ($number === false || $number < 0 || $number > 20) {
        return null;
    }

    return round((float) $number, 2);
}

function calculate_simple_grade(?float $devoir1, ?float $devoir2, ?float $devoir3, ?float $noteExamen): array
{
    $noteClasse = null;
    $noteFinale = null;

    if ($devoir1 !== null && $devoir2 !== null && $devoir3 !== null) {
        $noteClasse = round(($devoir1 + $devoir2 + $devoir3) / 3, 2);
    }

    if ($noteClasse !== null && $noteExamen !== null) {
        $noteFinale = round(($noteClasse + ($noteExamen * 2)) / 3, 2);
    }

    return [
        "note_classe" => $noteClasse,
        "note_finale" => $noteFinale,
    ];
}

function save_simple_grade(PDO $pdo, string $matEtudiant, int $moduleId, ?int $semestreId, array $values, string $updatedBy): void
{
    $calculated = calculate_simple_grade(
        $values["devoir_1"],
        $values["devoir_2"],
        $values["devoir_3"],
        $values["note_examen"]
    );

    $stmt = $pdo->prepare("
        SELECT ID
        FROM note
        WHERE MAT_ET = ?
        AND module_id = ?
        AND (semestre_id <=> ?)
        LIMIT 1
    ");
    $stmt->execute([$matEtudiant, $moduleId, $semestreId]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $update = $pdo->prepare("
            UPDATE note
            SET semestre_id = ?, devoir_1 = ?, devoir_2 = ?, devoir_3 = ?, note_classe = ?, note_examen = ?,
                note_finale = ?, valeur = ?, poids = 100, penalite = 0, updated_by = ?
            WHERE ID = ?
        ");
        $update->execute([
            $semestreId,
            $values["devoir_1"],
            $values["devoir_2"],
            $values["devoir_3"],
            $calculated["note_classe"],
            $values["note_examen"],
            $calculated["note_finale"],
            $calculated["note_finale"] ?? 0,
            $updatedBy,
            $existingId,
        ]);

        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO note
        (MAT_ET, module_id, semestre_id, valeur, poids, penalite, devoir_1, devoir_2, devoir_3, note_classe, note_examen, note_finale, created_by, updated_by)
        VALUES (?, ?, ?, ?, 100, 0, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([
        $matEtudiant,
        $moduleId,
        $semestreId,
        $calculated["note_finale"] ?? 0,
        $values["devoir_1"],
        $values["devoir_2"],
        $values["devoir_3"],
        $calculated["note_classe"],
        $values["note_examen"],
        $calculated["note_finale"],
        $updatedBy,
        $updatedBy,
    ]);
}

function existing_grades_by_student(PDO $pdo, int $moduleId, ?int $semestreId = null): array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM note
        WHERE module_id = ?
        AND (semestre_id <=> ?)
    ");
    $stmt->execute([$moduleId, $semestreId]);

    $grades = [];
    foreach ($stmt->fetchAll() as $grade) {
        $grades[$grade["MAT_ET"]] = $grade;
    }

    return $grades;
}

function student_semesters(PDO $pdo, int $classeId): array
{
    $stmt = $pdo->prepare("
        SELECT id, nom, ordre
        FROM classe_semestres
        WHERE classe_id = ?
        ORDER BY ordre ASC, nom ASC
    ");
    $stmt->execute([$classeId]);

    return $stmt->fetchAll();
}

function student_semester_average(PDO $pdo, string $matEtudiant, int $classeId, int $semestreId): array
{
    $stmt = $pdo->prepare("
        SELECT
            cs.id,
            cs.nom,
            COUNT(DISTINCT cm.module_id) AS total_modules,
            COUNT(DISTINCT CASE WHEN n.note_finale IS NOT NULL THEN cm.module_id END) AS notes_finales,
            SUM(CASE WHEN n.note_finale IS NOT NULL THEN n.note_finale ELSE 0 END) AS somme_notes
        FROM classe_semestres cs
        LEFT JOIN classe_modules cm ON cm.semestre_id = cs.id AND cm.classe_id = cs.classe_id
        LEFT JOIN note n ON n.module_id = cm.module_id
            AND n.semestre_id = cs.id
            AND n.MAT_ET = ?
        WHERE cs.classe_id = ?
        AND cs.id = ?
        GROUP BY cs.id, cs.nom
        LIMIT 1
    ");
    $stmt->execute([$matEtudiant, $classeId, $semestreId]);
    $row = $stmt->fetch();

    if (!$row) {
        return [
            "semestre_id" => $semestreId,
            "semestre_nom" => "Semestre",
            "total_modules" => 0,
            "notes_finales" => 0,
            "complete" => false,
            "moyenne" => null,
        ];
    }

    $totalModules = (int) $row["total_modules"];
    $notesFinales = (int) $row["notes_finales"];
    $complete = $totalModules > 0 && $notesFinales === $totalModules;

    return [
        "semestre_id" => (int) $row["id"],
        "semestre_nom" => $row["nom"],
        "total_modules" => $totalModules,
        "notes_finales" => $notesFinales,
        "complete" => $complete,
        "moyenne" => $complete ? round(((float) $row["somme_notes"]) / $totalModules, 2) : null,
    ];
}
