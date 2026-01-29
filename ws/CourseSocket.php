    <?php

    use JwtHelper;
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;

    class CourseSocket implements MessageComponentInterface
    {
        protected \SplObjectStorage $clients;

        public function __construct()
        {
            $this->clients = new \SplObjectStorage();
        }

        public function onOpen(ConnectionInterface $conn)
        {
            $this->clients->attach($conn);
        }

        public function onClose(ConnectionInterface $conn)
        {
            $this->clients->detach($conn);
        }

        public function onError(ConnectionInterface $conn, \Exception $e)
        {
            $conn->close();
        }

        public function onMessage(ConnectionInterface $from, $msg)
        {
            $data = json_decode($msg, true);
            if (!$data || empty($data['type'])) {
                echo "INVALID MESSAGE\n";
                return;
            }

            switch ($data['type']) {
                case 'course.create':
                    echo "HANDLE course.create\n";
                    $this->handleCreateCourse($from, $data);
                    break;
            }
        }
        protected function handleCreateCourse(ConnectionInterface $conn, array $data)
        {
            $userId = $this->auth($data['token'] ?? null);
            echo $userId;
            if (!$userId) {
                $conn->send(json_encode([
                    'type' => 'error',
                    'message' => 'Unauthorized'
                ]));
                return;
            }

            $payload = $data['payload'] ?? [];

            if (empty($payload['title'])) {
                $conn->send(json_encode([
                    'type' => 'course.create.error',
                    'errors' => [
                        'title' => ['Название курса обязательно']
                    ]
                ]));
                return;
            }

            $course = $this->createCourse($userId, $payload);

            if (!$course) {
                $conn->send(json_encode([
                    'type' => 'course.create.error',
                    'message' => 'Ошибка создания курса'
                ]));
                return;
            }

            $response = [
                'type' => 'course.created',
                'data' => $course
            ];

            foreach ($this->clients as $client) {
                $client->send(json_encode($response));
            }
        }
        protected function createCourse(int $userId, array $data): ?array
        {
            $pdo = new \PDO(
                'mysql:host=MySQL-8.0;dbname=rch;charset=utf8mb4',
                'root',
                '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare("
            INSERT INTO course (title, description, is_public, owner_id)
            VALUES (:title, :description, :is_public, :owner_id)
        ");

            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':is_public' => (int)($data['is_public'] ?? true),
                ':owner_id' => $userId
            ]);

            return [
                'id' => (int)$pdo->lastInsertId(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_public' => (bool)($data['is_public'] ?? true),
                'owner_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        protected function auth(?string $token): ?int
        {

            if (!$token) {
                return null;
            }

            try {
                $pdo = new \PDO(
                    'mysql:host=MySQL-8.0;dbname=rch;charset=utf8mb4',
                    'root',
                    '',
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );

                // Предположим, что токены хранятся в таблице users в поле `api_token`
                $stmt = $pdo->prepare("SELECT id FROM user WHERE token = :token LIMIT 1");
                $stmt->execute([':token' => $token]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$user) {
                    return null;
                }

                return (int)$user['id'];
            } catch (\PDOException $e) {
                echo "DB ERROR:\n";
                echo $e->getMessage() . "\n";
                return null;
            }
        }
    }
