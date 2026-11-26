<?php
declare(strict_types=1);
/** Keranjang permanen per akun pelanggan. */
class CartModel extends BaseModel {
    public function cartId(int $customerId,
    bool $create = true): ?int {
        $s = $this->db->prepare("SELECT id FROM keranjang WHERE pelanggan_id=? LIMIT 1");
        $s->execute([$customerId]);
        $id = $s->fetchColumn();
        if ($id) return (int)$id;
        if (!$create) return null;
        $this->db->prepare("INSERT INTO keranjang(pelanggan_id) VALUES(?)")->execute([$customerId]);
        return (int)$this->db->lastInsertId();
    }
    public function items(int $customerId): array {
        $s = $this->db->prepare("SELECT ki.*,m.nama_produk,m.harga,m.foto,m.status_aktif,km.nama kategori,
                t.nama topping_name,t.harga_tambahan,COALESCE(sp.jumlah_stok,0) stok
            FROM keranjang_item ki
            JOIN keranjang k ON k.id=ki.keranjang_id
            JOIN menu m ON m.id=ki.menu_id
            JOIN kategori_menu km ON km.id=m.kategori_id
            LEFT JOIN topping t ON t.id=ki.topping_id
            LEFT JOIN stok_produk sp ON sp.menu_id=m.id
            WHERE k.pelanggan_id=?
            ORDER BY ki.id ASC");
        $s->execute([$customerId]);
        $rows = $s->fetchAll();
        foreach ($rows as &$r) {
            $r['unit_price'] = (float)$r['harga'] + (float)($r['harga_tambahan'] ?? 0);
            $r['subtotal'] = $r['unit_price'] * (int)$r['qty'];
        }
        unset($r);
        return $rows;
    }
    public function count(int $customerId): int {
        $s = $this->db->prepare("SELECT COALESCE(SUM(ki.qty),0) FROM keranjang_item ki JOIN keranjang k ON k.id=ki.keranjang_id WHERE k.pelanggan_id=?");
        $s->execute([$customerId]);
        return (int)$s->fetchColumn();
    }
    public function subtotal(int $customerId): float {
        $t = 0.0;
        foreach ($this->items($customerId) as $i) $t += (float)$i['subtotal'];
        return $t;
    }
    public function add(int $customerId,
    int $menuId,
    int $qty,
    ?int $toppingId,
    ?int $level): void {
        $qty = max(1,
        $qty);
        $m = $this->db->prepare("SELECT m.*,COALESCE(sp.jumlah_stok,0) stok FROM menu m LEFT JOIN stok_produk sp ON sp.menu_id=m.id WHERE m.id=? AND m.status_aktif=1 LIMIT 1");
        $m->execute([$menuId]);
        $menu = $m->fetch();
        if (!$menu) throw new RuntimeException('Produk tidak tersedia.');
        if ((int)$menu['stok'] < $qty) throw new RuntimeException('Stok produk tidak mencukupi.');
        if (!(int)$menu['punya_topping']) $toppingId = null;
        if ($toppingId) {
            $t = $this->db->prepare("SELECT 1 FROM menu_topping mt JOIN topping t ON t.id=mt.topping_id WHERE mt.menu_id=? AND mt.topping_id=? AND t.status_aktif=1");
            $t->execute([$menuId,
            $toppingId]);
            if (!$t->fetchColumn()) throw new RuntimeException('Topping tidak valid untuk produk ini.');
        }
        if (!(int)$menu['punya_level_pedas']) $level = null;
        if ($level !== null && ($level < 0 || $level > 5)) throw new RuntimeException('Level pedas tidak valid.');
        $cartId = $this->cartId($customerId);
        $existing = $this->db->prepare("SELECT id,qty FROM keranjang_item WHERE keranjang_id=? AND menu_id=? AND topping_id <=> ? AND level_pedas <=> ? LIMIT 1");
        $existing->execute([$cartId,
        $menuId,
        $toppingId,
        $level]);
        $row = $existing->fetch();
        if ($row) {
            $newQty = (int)$row['qty'] + $qty;
            if ((int)$menu['stok'] < $newQty) throw new RuntimeException('Jumlah melebihi stok tersedia.');
            $this->db->prepare("UPDATE keranjang_item SET qty=? WHERE id=?")->execute([$newQty,
            (int)$row['id']]);
        }  else {
            $this->db->prepare("INSERT INTO keranjang_item(keranjang_id,menu_id,topping_id,level_pedas,qty) VALUES(?,?,?,?,?)")
            ->execute([$cartId,
            $menuId,
            $toppingId,
            $level,
            $qty]);
        }
    }
    public function update(int $itemId,
    int $customerId,
    int $qty): void {
        $qty = max(0,
        $qty);
        $s = $this->db->prepare("SELECT ki.id,COALESCE(sp.jumlah_stok,0) stok FROM keranjang_item ki JOIN keranjang k ON k.id=ki.keranjang_id JOIN menu m ON m.id=ki.menu_id LEFT JOIN stok_produk sp ON sp.menu_id=m.id WHERE ki.id=? AND k.pelanggan_id=?");
        $s->execute([$itemId,
        $customerId]);
        $row = $s->fetch();
        if (!$row) throw new RuntimeException('Item keranjang tidak ditemukan.');
        if ($qty === 0) {
            $this->remove($itemId,
            $customerId);
            return;
        }
        if ((int)$row['stok'] < $qty) throw new RuntimeException('Jumlah melebihi stok tersedia.');
        $this->db->prepare("UPDATE keranjang_item SET qty=? WHERE id=?")->execute([$qty,
        $itemId]);
    }
    public function remove(int $itemId,
    int $customerId): void {
        $this->db->prepare("DELETE ki FROM keranjang_item ki JOIN keranjang k ON k.id=ki.keranjang_id WHERE ki.id=? AND k.pelanggan_id=?")
        ->execute([$itemId,
        $customerId]);
    }
    public function clear(int $customerId): void {
        $this->db->prepare("DELETE ki FROM keranjang_item ki JOIN keranjang k ON k.id=ki.keranjang_id WHERE k.pelanggan_id=?")
        ->execute([$customerId]);
    }
    /** Bentuk data yang dipakai OrderModel::createOrder(). */
    public function checkoutItems(int $customerId): array {
        $items = [];
        $total = 0.0;
        $qty = 0;
        foreach ($this->items($customerId) as $i) {
            if (!(int)$i['status_aktif']) continue;
            $items[] = [
            'menu_id' => (int)$i['menu_id'],
            'nama_produk' => $i['nama_produk'],
            'foto' => $i['foto'] ?? '',
            'qty' => (int)$i['qty'],
            'unit_price' => (float)$i['unit_price'],
            'subtotal' => (float)$i['subtotal'],
            'topping_id' => $i['topping_id'] !== null ? (int)$i['topping_id'] : null,
            'topping_name' => $i['topping_name'] ?? null,
            'level_pedas' => $i['level_pedas'] !== null ? (int)$i['level_pedas'] : null,
            'is_gratis' => 0,
            ];
            $total += (float)$i['subtotal'];
            $qty += (int)$i['qty'];
        }
        return ['items' => $items,
        'total' => $total,
        'qty' => $qty];
    }
}
