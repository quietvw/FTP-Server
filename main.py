from pyftpdlib.authorizers import DummyAuthorizer
from pyftpdlib.handlers import FTPHandler
from pyftpdlib.servers import FTPServer
import sqlite3


conn = sqlite3.connect("database.db")
cursor = conn.cursor()

# Create 'settings' table if it doesn't exist
cursor.execute('''
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY,
    setting TEXT NOT NULL,
    value TEXT NOT NULL
)
''')

# Create 'users' table if it doesn't exist
cursor.execute('''
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    path TEXT NOT NULL,
    permissions TEXT NOT NULL
)
''')

# Optional: Insert initial data if table is empty
cursor.execute('SELECT COUNT(*) FROM settings')
if cursor.fetchone()[0] == 0:
    cursor.executemany('INSERT INTO settings (id, setting, value) VALUES (?, ?, ?)', [
        (1, 'Host', '0.0.0.0'),
        (2, 'Port', '21')
    ])

# Commit and close
conn.commit()

authorizer = DummyAuthorizer()
authorizer.add_user("user", "12345", "/home/giampaolo", perm="elradfmwMT")
authorizer.add_anonymous("/home/nobody")

handler = FTPHandler
handler.authorizer = authorizer

server = FTPServer(("127.0.0.1", 21), handler)
server.serve_forever()

