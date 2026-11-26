<?php
declare(strict_types=1);
class AuthModel extends BaseModel {
    public function findCustomerByEmail(string $email): ?array {
        $s=$this->db->prepare("SELECT * FROM pelanggan WHERE email=? LIMIT 1");
        $s->execute([$email]);
        return $s->fetch() ?: null;
    }
    public function findCustomer(int $id): ?array {
        $s=$this->db->prepare("SELECT * FROM pelanggan WHERE id=? LIMIT 1");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public function createCustomer(array $d): int {
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare("INSERT INTO pelanggan(username,nama,email,no_hp,password) VALUES(?,?,?,?,?)");
            $s->execute([$d['username'],
            $d['nama'],
            $d['email'],
            $d['no_hp'] ?: null,
            password_hash($d['password'],
            PASSWORD_BCRYPT)]);
            $id=(int)$this->db->lastInsertId();
            $s=$this->db->prepare("INSERT INTO membership(pelanggan_id,level,tanggal_gabung) VALUES(?,?,CURDATE())");
            $s->execute([$id,
            'REGULER']);
            $this->db->commit();
            return $id;
        }  catch(Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function updateCustomer(int $id,
    array $d): bool {
        $sql="UPDATE pelanggan SET nama=?,email=?,no_hp=?";
        $params=[$d['nama'],
        $d['email'],
        $d['no_hp'] ?: null];
        if (!empty($d['password'])) {
            $sql.=",password=?";
            $params[]=password_hash($d['password'],
            PASSWORD_BCRYPT);
        }
        $sql.=" WHERE id=?";
        $params[]=$id;
        return $this->db->prepare($sql)->execute($params);
    }
    public function createReset(int $id,
    string $hashedToken,
    string $expires): void {
        $this->db->prepare("UPDATE password_resets SET used_at=NOW() WHERE pelanggan_id=? AND used_at IS NULL")->execute([$id]);
        $this->db->prepare("INSERT INTO password_resets(pelanggan_id,token,expired_at) VALUES(?,?,?)")->execute([$id,
        $hashedToken,
        $expires]);
    }
    public function findValidReset(string $hashedToken): ?array {
        $s=$this->db->prepare("SELECT * FROM password_resets WHERE token=? AND used_at IS NULL AND expired_at>NOW() LIMIT 1");
        $s->execute([$hashedToken]);
        return $s->fetch() ?: null;
    }
    public function resetPassword(int $resetId,
    int $customerId,
    string $password): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE pelanggan SET password=? WHERE id=?")->execute([password_hash($password,
            PASSWORD_BCRYPT),
            $customerId]);
            $this->db->prepare("UPDATE password_resets SET used_at=NOW() WHERE id=?")->execute([$resetId]);
            $this->db->commit();
        }  catch(Throwable $e){
            $this->db->rollBack();
            throw $e;
        }
    }
}
