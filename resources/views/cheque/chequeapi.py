from flask import Flask, request, jsonify
import psycopg2
import psycopg2.extras

app = Flask(__name__)

def get_db():
    return psycopg2.connect(
        dbname="cheque",
        user="postgres",
        password="Ekapab2025",
        host="localhost",
        port="5432"
    )

# ==================== Cheques ====================
@app.route('/cheques', methods=['GET'])
@app.route('/api/cheques', methods=['GET'])
def get_cheques():
    conn = get_db()
    cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)
    
    # รับ parameters สำหรับ DataTables server-side processing
    draw = request.args.get('draw', type=int)
    start = request.args.get('start', type=int, default=0)
    length = request.args.get('length', type=int, default=50)
    search_value = request.args.get('search[value]', default='')
    order_column_idx = request.args.get('order[0][column]', type=int, default=3)
    order_dir = request.args.get('order[0][dir]', default='desc')
    
    # Column mapping (ตามลำดับใน DataTable)
    columns = ['branch_code', 'bank', 'cheque_number', 'date', 'payee', 'amount', 'printed_at']
    order_column = columns[order_column_idx] if order_column_idx < len(columns) else 'id'
    
    # ถ้าไม่มี draw parameter แสดงว่าเป็น request แบบเก่า (ไม่ใช่ DataTables)
    if draw is None:
        cur.execute("SELECT * FROM cheques ORDER BY id DESC")
        rows = cur.fetchall()
        cur.close()
        conn.close()
        return jsonify([dict(row) for row in rows])
    
    # สร้าง query สำหรับ server-side processing
    where_clause = ""
    params = []
    
    if search_value:
        where_clause = """
            WHERE CAST(branch_code AS TEXT) ILIKE %s 
            OR CAST(bank AS TEXT) ILIKE %s 
            OR CAST(cheque_number AS TEXT) ILIKE %s 
            OR CAST(payee AS TEXT) ILIKE %s
        """
        search_param = f'%{search_value}%'
        params = [search_param, search_param, search_param, search_param]
    
    # นับจำนวนทั้งหมด
    cur.execute("SELECT COUNT(*) FROM cheques")
    records_total = cur.fetchone()[0]
    
    # นับจำนวนที่กรองแล้ว
    if where_clause:
        cur.execute(f"SELECT COUNT(*) FROM cheques {where_clause}", params)
        records_filtered = cur.fetchone()[0]
    else:
        records_filtered = records_total
    
    # ดึงข้อมูลแบบแบ่งหน้า
    query = f"""
        SELECT * FROM cheques 
        {where_clause}
        ORDER BY {order_column} {order_dir}
        LIMIT %s OFFSET %s
    """
    params.extend([length, start])
    
    cur.execute(query, params)
    rows = cur.fetchall()
    cur.close()
    conn.close()
    
    # ส่งข้อมูลในรูปแบบที่ DataTables ต้องการ
    return jsonify({
        'draw': draw,
        'recordsTotal': records_total,
        'recordsFiltered': records_filtered,
        'data': [dict(row) for row in rows]
    })

@app.route('/cheques', methods=['POST'])
@app.route('/api/cheques', methods=['POST'])
def add_cheque():
    data = request.json
    conn = get_db()
    cur = conn.cursor()
    cur.execute("""
        INSERT INTO cheques (branch_code, bank, cheque_number, date, payee, amount)
        VALUES (%s, %s, %s, %s, %s, %s) RETURNING id
    """, (
        data['branch_code'], data['bank'], data['cheque_number'],
        data['date'], data['payee'], data['amount']
    ))
    cheque_id = cur.fetchone()[0]
    conn.commit()
    cur.close()
    conn.close()
    return jsonify({'id': cheque_id}), 201

@app.route('/cheques/<int:cheque_id>', methods=['DELETE'])
@app.route('/api/cheques/<int:cheque_id>', methods=['DELETE'])
def delete_cheque(cheque_id):
    conn = get_db()
    cur = conn.cursor()
    cur.execute("DELETE FROM cheques WHERE id = %s", (cheque_id,))
    conn.commit()
    cur.close()
    conn.close()
    return jsonify({'status': 'ok'})

# ==================== Branches ====================
@app.route('/branches', methods=['GET'])
@app.route('/api/branches', methods=['GET'])
def get_branches():
    conn = get_db()
    cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)
    cur.execute("SELECT * FROM branches ORDER BY code")
    rows = cur.fetchall()
    cur.close()
    conn.close()
    return jsonify([dict(row) for row in rows])

@app.route('/branches', methods=['POST'])
@app.route('/api/branches', methods=['POST'])
def add_branch():
    data = request.json
    conn = get_db()
    cur = conn.cursor()
    cur.execute("""
        INSERT INTO branches (code, name) VALUES (%s, %s)
        ON CONFLICT (code) DO NOTHING
    """, (data['code'], data['name']))
    conn.commit()
    cur.close()
    conn.close()
    return jsonify({'status': 'ok'})

@app.route('/branches/<code>', methods=['DELETE'])
@app.route('/api/branches/<code>', methods=['DELETE'])
def delete_branch(code):
    conn = get_db()
    cur = conn.cursor()
    cur.execute("DELETE FROM branches WHERE code = %s", (code,))
    conn.commit()
    cur.close()
    conn.close()
    return jsonify({'status': 'ok'})

# ==================== Templates ====================
@app.route('/templates', methods=['GET'])
@app.route('/api/templates', methods=['GET'])
def get_templates():
    conn = get_db()
    cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)
    cur.execute("SELECT * FROM cheque_templates ORDER BY id DESC")
    rows = cur.fetchall()
    cur.close()
    conn.close()
    return jsonify([dict(row) for row in rows])

@app.route('/templates', methods=['POST'])
@app.route('/api/templates', methods=['POST'])
def add_template():
    data = request.json
    conn = get_db()
    cur = conn.cursor()
    # ใช้ UPSERT: ถ้ามี bank อยู่แล้วให้ UPDATE, ถ้าไม่มีให้ INSERT
    cur.execute("""
        INSERT INTO cheque_templates (bank, template_json)
        VALUES (%s, %s)
        ON CONFLICT (bank) 
        DO UPDATE SET template_json = EXCLUDED.template_json
    """, (data['bank'], psycopg2.extras.Json(data['template_json'])))
    conn.commit()
    cur.close()
    conn.close()
    return jsonify({'status': 'ok'})


# --- เพิ่มใน chequeapi.py (ท้ายไฟล์ก่อน if __name__ == '__main__':) ---

@app.route('/payees', methods=['GET'])
@app.route('/api/payees', methods=['GET'])
def list_payees():
    q = request.args.get('q', '', type=str)
    limit = request.args.get('limit', 10, type=int)
    branch = request.args.get('branch', '', type=str)

    conn = get_db()
    cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    where = []
    params = []
    if q:
        where.append("payee ILIKE %s")
        params.append(f"%{q}%")
    if branch:
        where.append("branch_code = %s")
        params.append(branch)

    where_sql = ("WHERE " + " AND ".join(where)) if where else ""
    sql = f"""
        SELECT payee, COUNT(*) as cnt
        FROM cheques
        {where_sql}
        GROUP BY payee
        HAVING payee IS NOT NULL AND payee <> ''
        ORDER BY cnt DESC, payee ASC
        LIMIT %s
    """
    params.append(limit)

    cur.execute(sql, params)
    rows = cur.fetchall()
    cur.close()
    conn.close()

    return jsonify([row['payee'] for row in rows])

@app.route('/cheques/next', methods=['GET'])
@app.route('/api/cheques/next', methods=['GET'])
def next_cheque_number():
    branch = request.args.get('branch', '', type=str)
    bank   = request.args.get('bank', '', type=str)

    if not branch:
        return jsonify({'error': 'branch required'}), 400

    conn = get_db()
    cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    where = ["branch_code = %s"]
    params = [branch]
    if bank:
        where.append("bank = %s")
        params.append(bank)

    where_sql = "WHERE " + " AND ".join(where)
    # เอาใบล่าสุดในสาขานั้น (จะเรียง id หรือ date ก็ได้ — ใช้ id สั้นๆก่อน)
    cur.execute(f"""
        SELECT cheque_number 
        FROM cheques
        {where_sql}
        ORDER BY id DESC
        LIMIT 1
    """, params)

    row = cur.fetchone()
    cur.close()
    conn.close()

    last_no = (row['cheque_number'] if row and row['cheque_number'] else '').strip()

    def inc_str(s: str) -> str:
        import re
        m = re.match(r'^(.*?)(\d+)([^0-9]*)$', s or '')
        if not m:
            return ''  # ถ้าไม่มีเลขท้าย ก็ไม่เดา
        prefix, num, suffix = m.groups()
        width = len(num)
        next_num = str(int(num) + 1).zfill(width)
        return f"{prefix}{next_num}{suffix}"

    next_no = inc_str(last_no) if last_no else ''
    return jsonify({'last': last_no, 'next': next_no})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5500)