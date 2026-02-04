    <?php

    use JwtHelper;
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;

    class CourseSocket implements MessageComponentInterface
    {
        protected \SplObjectStorage $clients;
        protected \PDO $pdo;

        public function __construct()
        {
            $this->clients = new \SplObjectStorage();

            $this->pdo = new \PDO(
                'mysql:host=MySQL-8.0;dbname=rch;charset=utf8mb4',
                'root',
                '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
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

                case 'element.create':
                    $this->handleCreateElement($from, $data);
                    break;
                case 'element.update':
                    $this->handleUpdateElement($from, $data);
                    break;
                case 'element.delete':
                    $this->handleDeleteElement($from, $data);
                    break;

                case 'element.updated':
                    foreach ($this->clients as $client) {
                        $client->send($msg);
                    }
                    break;
                case 'element.created':
                    foreach ($this->clients as $client) {
                        $client->send($msg);
                    }
                    break;
                case 'element.deleted':
                    foreach ($this->clients as $client) {
                        $client->send($msg);
                    }
                    break;

                case 'course.isPublic':
                    foreach ($this->clients as $client) {
                        $client->send($msg);
                    }
                    break;
                case 'course.created':
                    foreach ($this->clients as $client) {
                        $client->send($msg);
                    }
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
        protected function handleDeleteElement(ConnectionInterface $conn, array $data)
        {
            $userId = $this->auth($data['token'] ?? null);

            if (!$userId) {
                $conn->send(json_encode([
                    'type' => 'error',
                    'message' => 'Unauthorized'
                ]));
                return;
            }

            $payload = $data['payload'] ?? [];

            if (empty($payload['id']) || empty($payload['course_id'])) {
                $conn->send(json_encode([
                    'type' => 'element.delete.error',
                    'message' => 'id and course_id required'
                ]));
                return;
            }

            // проверяем курс
            $stmt = $this->pdo->prepare("
        SELECT id FROM course WHERE id = :id LIMIT 1
    ");
            $stmt->execute([':id' => $payload['course_id']]);
            $course = $stmt->fetch();

            if (!$course) {
                $conn->send(json_encode([
                    'type' => 'element.delete.error',
                    'message' => 'Course not found'
                ]));
                return;
            }

            // проверяем элемент
            $stmt = $this->pdo->prepare("
        SELECT id, course_id, file_url
        FROM course_element
        WHERE id = :id
        LIMIT 1
    ");
            $stmt->execute([':id' => $payload['id']]);
            $element = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$element) {
                $conn->send(json_encode([
                    'type' => 'element.delete.error',
                    'message' => 'Element not found'
                ]));
                return;
            }

            if ((int)$element['course_id'] !== (int)$payload['course_id']) {
                $conn->send(json_encode([
                    'type' => 'element.delete.error',
                    'message' => 'Element not in this course'
                ]));
                return;
            }

            // удаляем файл если есть
            if (!empty($element['file_url'])) {
                $filePath = 'uploads/' . $element['file_url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // удаляем запись
            $stmt = $this->pdo->prepare("
        DELETE FROM course_element WHERE id = :id
    ");
            $stmt->execute([':id' => $payload['id']]);

            // broadcast
            $response = [
                'type' => 'element.deleted',
                'data' => [
                    'id' => (int)$payload['id'],
                    'course_id' => (int)$payload['course_id']
                ]
            ];

            foreach ($this->clients as $client) {
                $client->send(json_encode($response));
            }
        }

        protected function handleCreateElement(ConnectionInterface $conn, array $data)
        {
            $userId = $this->auth($data['token'] ?? null);

            if (!$userId) {
                $conn->send(json_encode([
                    'type' => 'error',
                    'message' => 'Unauthorized'
                ]));
                return;
            }

            $payload = $data['payload'] ?? [];

            if (empty($payload['course_id']) || empty($payload['title'])) {
                $conn->send(json_encode([
                    'type' => 'element.create.error',
                    'message' => 'Invalid payload'
                ]));
                return;
            }

            $stmt = $this->pdo->prepare("
        INSERT INTO course_element
        (course_id, title, content_url, file_url, x, y, styles)
        VALUES
        (:course_id, :title, :content_url, :file_url, :x, :y, :styles)
    ");

            $stmt->execute([
                ':course_id' => $payload['course_id'],
                ':title' => $payload['title'],
                ':content_url' => $payload['content_url'] ?? null,
                ':file_url' => $payload['file_url'] ?? null,
                ':x' => $payload['x'] ?? null,
                ':y' => $payload['y'] ?? null,
                ':styles' => isset($payload['styles'])
                    ? json_encode($payload['styles'])
                    : null,
            ]);

            $elementId = (int)$this->pdo->lastInsertId();

            $response = [
                'type' => 'element.created',
                'data' => [
                    'id' => $elementId,
                    'course_id' => $payload['course_id'],
                    'title' => $payload['title'],
                    'content_url' => $payload['content_url'] ?? null,
                    'file_url' => $payload['file_url'] ?? null,
                    'x' => $payload['x'] ?? null,
                    'y' => $payload['y'] ?? null,
                    'styles' => $payload['styles'] ?? null
                ]
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
        protected function handleUpdateElement(ConnectionInterface $conn, array $data)
        {
            $userId = $this->auth($data['token'] ?? null);

            if (!$userId) {
                $conn->send(json_encode([
                    'type' => 'error',
                    'message' => 'Unauthorized'
                ]));
                return;
            }

            $payload = $data['payload'] ?? [];

            if (empty($payload['id']) || empty($payload['course_id'])) {
                $conn->send(json_encode([
                    'type' => 'element.update.error',
                    'message' => 'id and course_id required'
                ]));
                return;
            }

            // Проверяем курс
            $stmt = $this->pdo->prepare("
        SELECT id FROM course WHERE id = :id LIMIT 1
    ");
            $stmt->execute([':id' => $payload['course_id']]);
            $course = $stmt->fetch();

            if (!$course) {
                $conn->send(json_encode([
                    'type' => 'element.update.error',
                    'message' => 'Course not found'
                ]));
                return;
            }

            // Проверяем элемент
            $stmt = $this->pdo->prepare("
        SELECT id, course_id
        FROM course_element
        WHERE id = :id
        LIMIT 1
    ");
            $stmt->execute([':id' => $payload['id']]);
            $element = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$element) {
                $conn->send(json_encode([
                    'type' => 'element.update.error',
                    'message' => 'Element not found'
                ]));
                return;
            }

            // Проверяем принадлежность курсу
            if ((int)$element['course_id'] !== (int)$payload['course_id']) {
                $conn->send(json_encode([
                    'type' => 'element.update.error',
                    'message' => 'Element does not belong to course'
                ]));
                return;
            }

            // Обновление
            // Обновление
            $fields = [];
            $params = [':id' => $payload['id']];

            $map = [
                'title' => 'title',
                'content_url' => 'content_url',
                'file_url' => 'file_url',
                'x' => 'x',
                'y' => 'y',
                'styles' => 'styles',
            ];

            foreach ($map as $key => $column) {
                if (array_key_exists($key, $payload)) {
                    $fields[] = "$column = :$key";

                    if ($key === 'styles') {
                        $params[":$key"] = json_encode($payload[$key]);
                    } else {
                        $params[":$key"] = $payload[$key];
                    }
                }
            }

            if (empty($fields)) {
                $conn->send(json_encode([
                    'type' => 'element.update.error',
                    'message' => 'Nothing to update'
                ]));
                return;
            }

            $sql = "
    UPDATE course_element
    SET " . implode(', ', $fields) . "
    WHERE id = :id
";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $response = [
                'type' => 'element.updated',
                'data' => $payload
            ];

            foreach ($this->clients as $client) {
                $client->send(json_encode($response));
            }
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
