<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-exclamation-triangle text-danger me-2"></i> Správa reklamácií</h2>
        <small class="text-muted">Prehľad všetkých nahlásených poškodených dielcov</small>
    </div>
</div>

<div class="card shadow-sm border-0 border-top border-danger border-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Dátum podania</th>
                        <th>K objednávke</th>
                        <th>Nahlasovateľ</th>
                        <th>Stav</th>
                        <th class="text-end pe-3">Akcia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($complaints)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Zatiaľ neboli podané žiadne reklamácie. Skvelá práca!</td></tr>
                    <?php else: ?>
                        <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-muted">#<?= $c['id_reklamacia'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($c['datum_podania'])) ?></td>
                            <td>
                                <a href="index.php?page=view_order&id=<?= $c['id_objednavka'] ?>" class="text-decoration-none fw-bold">
                                    <?= htmlspecialchars($c['cislo_objednavky']) ?>
                                </a>
                            </td>
                            <td>
                                <?= htmlspecialchars($c['meno'] . ' ' . $c['priezvisko']) ?>
                                <?php if(isset($c['obchodny_nazov'])): ?>
                                    <br><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['obchodny_nazov']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if ($c['stav'] == 'Prijatá') $badge = 'bg-warning text-dark';
                                    if ($c['stav'] == 'V riešení') $badge = 'bg-primary';
                                    if ($c['stav'] == 'Schválená') $badge = 'bg-success';
                                    if ($c['stav'] == 'Zamietnutá') $badge = 'bg-danger';
                                    if ($c['stav'] == 'Vybavená') $badge = 'bg-dark';
                                ?>
                                <span class="badge <?= $badge ?>"><?= $c['stav'] ?></span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="index.php?page=view_complaint&id=<?= $c['id_reklamacia'] ?>" class="btn btn-sm btn-outline-danger">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>