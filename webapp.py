import sqlite3

from flask import Flask, jsonify, request

app = Flask(__name__)

def init_db():
    conn = sqlite3.connect('database.db')
    cursor = conn.cursor()

    # Create 'settings' table if it doesn't exist
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY,
            setting TEXT NOT NULL,
            value TEXT NOT NULL
        )''')

    # Create 'users' table if it doesn't exist
    cursor.excute('''
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            path TEXT NOT NULL,
            permissions TEXT NOT NULL
        )''')

    # Optional: Insert initial data if table is empty
    cursor.execute('SELECT COUNT(*) FROM settings')
    if cursor.fetchone()[0] == 0:
        cursor.executemany('INSERT INTO settings (id, setting, value) VALUES (?, ?, ?)',
                           [
                               (1, 'Host', '0.0.0.0'),
                               (2, 'Port', '21')
                           ])

    cursor.execute('SELECT COUNT(*) FROM users')
    if cursor.fetchone()[0] == 0:
        cursor.executemany('INSERT INTO users (id, username, password, path, permissions) VALUES (?, ?, ?, ?, ?)',
                           [
                               (1, 'admin', 'admin', 'admin', 'full')
                           ])

    conn.commit()

    authorizer = DummyAuthorizer()
    # Fetch all users
    cursor.execute('SELECT * FROM users')
    users = cursor.fetchall()

    # Loop through each user
    for user in users:
        user_id, username, password, path, permissions = user
        print(f"ID: {user_id}, Username: {username}, Path: {path}, Permissions: {permissions}")
        Path("root/" + path).mkdir(parents=True, exist_ok=True)
        authorizer.add_user(username, password, "root/" + path, perm="elradfmwMT")

    handler = FTPHandler
    handler.authorizer = authorizer
    cursor.execute("SELECT value FROM settings WHERE setting = 'Host'")
    host = cursor.fetchone()[0]
    cursor.execute("SELECT value FROM settings WHERE setting = 'Port'")
    port = int(cursor.fetchone()[0])
    server = FTPServer((host, port), handler)
    server.serve_forever()

    conn.close()

def get_db_connection():
    conn = sqlite3.connect('database.db')
    conn.row_factory = sqlite3.Row  # To return rows as dictionaries
    return conn

@app.route('/')
def index():
    return 'Welcome to the Flask SQLite Web Service!'

# Route to get all items
@app.route('/items', methods=['GET'])
def get_items():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM items')
    items = cursor.fetchall()
    conn.close()

    items_list = []
    for item in items:
        items_list.append({
            'id': item['id'],
            'name': item['name'],
            'description': item['description']
        })

    return jsonify(items_list)

# Route to get a single item by ID
@app.route('/items/<int:id>', methods=['GET'])
def get_item(id):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM items WHERE id = ?', (id,))
    item = cursor.fetchone()
    conn.close()

    if item is None:
        return jsonify({'error': 'Item not found'}), 404

    return jsonify({
        'id': item['id'],
        'name': item['name'],
        'description': item['description']
    })

# Route to create a new item
@app.route('/items', methods=['POST'])
def create_item():
    data = request.get_json()

    name = data.get('name')
    description = data.get('description')

    if not name:
        return jsonify({'error': 'Name is required'}), 400

    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('INSERT INTO items (name, description) VALUES (?, ?)', (name, description))
    conn.commit()
    conn.close()

    return jsonify({'message': 'Item created successfully'}), 201


# Flask runs on http://127.0.0.1:5000/ by default.
# To test: curl http://127.0.0.1:5000/
if __name__ == '__main__':
    app.run(debug=True)
