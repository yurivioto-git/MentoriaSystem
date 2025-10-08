-- SQL Migration Script for Multi-Course Feature

-- 1. Create the 'courses' table to store course information.
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- 2. Add the 'course_id' foreign key to the 'users' table.
-- This links users to a course. It's nullable for super admins.
ALTER TABLE users ADD COLUMN IF NOT EXISTS course_id INTEGER REFERENCES courses(id) ON DELETE SET NULL;

-- 3. Add the 'is_superadmin' flag to the 'users' table.
-- This identifies the user(s) with system-wide privileges.
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_superadmin BOOLEAN DEFAULT false;

-- 4. Update the 'role' check constraint to include 'coordinator' and 'student'.
-- This section is written to be safe to re-run.
DO $$
BEGIN
    -- Drop the old constraint if it exists
    IF EXISTS (SELECT 1 FROM pg_constraint WHERE conrelid = 'users'::regclass AND conname = 'users_role_check') THEN
        ALTER TABLE users DROP CONSTRAINT users_role_check;
    END IF;
    
    -- Change 'aluno' to 'student' for consistency
    UPDATE users SET role = 'student' WHERE role = 'aluno';
    
    -- Change 'admin' to 'coordinator' for existing admins that are not the superadmin
    -- The superadmin is identified by rm='admin'
    UPDATE users SET role = 'coordinator' WHERE role = 'admin' AND rm <> 'admin';

    -- Add the new, correct constraint
    ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student', 'coordinator', 'admin'));
END
$$;

-- 5. Transition the existing main 'admin' user to be the first 'super_admin'.
-- The 'admin' role is now reserved for super admins.
UPDATE users SET is_superadmin = true, role = 'admin' WHERE rm = 'admin';

-- 6. Seed the 'courses' table with some initial data.
-- ON CONFLICT clause prevents errors if the script is run multiple times.
INSERT INTO courses (name) VALUES ('Desenvolvimento de Sistemas'), ('Administração'), ('Enfermagem') ON CONFLICT (name) DO NOTHING;

-- 7. Assign existing students and new coordinators to a default course.
-- This avoids having orphaned users after the migration.
DO $$
DECLARE
    default_course_id INTEGER;
BEGIN
    -- Get the ID of the first course to use as a default
    SELECT id INTO default_course_id FROM courses LIMIT 1;

    -- If a course exists, update all users who don't have a course yet (except super admins)
    IF default_course_id IS NOT NULL THEN
        UPDATE users SET course_id = default_course_id WHERE course_id IS NULL AND is_superadmin = false;
    END IF;
END
$$;

