
import os
import sys
import platform
import shutil
import subprocess
from pathlib import Path

REPO_URL = "https://github.com/amir-starX/OmniCore.git"
PROJECT_DIR = "omnicore"


def info(message):
    print(f"\n[INFO] {message}")


def error(message):
    print(f"\n[ERROR] {message}", file=sys.stderr)


def run(command):
    try:
        subprocess.run(command, check=True)
        return True
    except (subprocess.CalledProcessError, OSError) as exc:
        error(f"Command failed: {exc}")
        return False


def refresh_path():
    paths = []

    if os.name == "nt":
        program_files = os.environ.get("ProgramFiles", r"C:\Program Files")
        local_app = os.environ.get("LOCALAPPDATA", "")

        paths.extend([
            os.path.join(program_files, "Git", "cmd"),
            os.path.join(program_files, "Git", "bin"),
            os.path.join(local_app, "Programs", "Git", "cmd"),
        ])

    current = os.environ.get("PATH", "")
    for path in paths:
        if os.path.isdir(path) and path not in current.split(os.pathsep):
            current = path + os.pathsep + current

    os.environ["PATH"] = current


def install_git():
    system = platform.system()
    info(f"Git is missing. Installing for {system}...")

    if system == "Windows":
        if not shutil.which("winget"):
            error("winget is unavailable.")
            print("Install Git manually: https://git-scm.com/download/win")
            return False

        success = run([
            "winget", "install",
            "--id", "Git.Git",
            "-e",
            "--source", "winget",
            "--accept-source-agreements",
            "--accept-package-agreements",
        ])

    elif system == "Darwin":
        if shutil.which("brew"):
            success = run(["brew", "install", "git"])
        else:
            error("Homebrew is not installed.")
            print("Install Git: https://git-scm.com/download/mac")
            return False

    elif system == "Linux":
        managers = [
            ("apt-get", ["sudo", "apt-get", "install", "-y", "git"],
             ["sudo", "apt-get", "update"]),
            ("dnf", ["sudo", "dnf", "install", "-y", "git"], None),
            ("yum", ["sudo", "yum", "install", "-y", "git"], None),
            ("pacman", ["sudo", "pacman", "-Sy", "--needed",
                        "--noconfirm", "git"], None),
            ("zypper", ["sudo", "zypper", "--non-interactive",
                        "install", "git"], None),
            ("apk", ["sudo", "apk", "add", "git"], None),
        ]

        manager = next(
            (item for item in managers if shutil.which(item[0])),
            None
        )

        if manager is None:
            error("No supported package manager was found.")
            print("Install Git: https://git-scm.com/downloads")
            return False

        _, install_command, update_command = manager

        if update_command and not run(update_command):
            error("Package index update failed.")
            return False

        success = run(install_command)

    else:
        error(f"Unsupported operating system: {system}")
        return False

    refresh_path()

    if not shutil.which("git"):
        error("Git is still unavailable. Restart the terminal and retry.")
        return False

    if not success:
        error("Git installation command failed.")
        return False

    info("Git installed successfully.")
    return True


def main():
    system = platform.system()

    if system not in ("Windows", "Linux", "Darwin"):
        error(f"Unsupported operating system: {system}")
        return 1

    info(f"Operating system: {system}")

    # Check Git
    if not shutil.which("git"):
        if not install_git():
            return 1

    run(["git", "--version"])

    current = Path.cwd()
    project = current / PROJECT_DIR

    if (current / ".git").exists():
        target = current
        info("Git repository detected in the current directory.")

    elif (project / ".git").exists():
        target = project
        info("Existing OmniCore repository detected.")

    else:
        if project.exists():
            error(
                f"'{PROJECT_DIR}' already exists but is not a Git repository."
            )
            return 1

        info("Cloning OmniCore...")

        if not run(["git", "clone", REPO_URL, str(project)]):
            return 1

        target = project

    os.chdir(target)

    try:
        subprocess.run(
            ["git", "rev-parse", "--is-inside-work-tree"],
            check=True,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
    except (subprocess.CalledProcessError, OSError):
        error("The target directory is not a valid Git repository.")
        return 1

    if not shutil.which("php"):
        error("PHP is missing: https://www.php.net/")
        return 1

    if not shutil.which("composer"):
        error("Composer is missing: https://getcomposer.org/download/")
        return 1

    if not (Path.cwd() / "composer.json").is_file():
        error("composer.json was not found in the repository.")
        return 1

    info("Running composer install...")

    if not run(["composer", "install"]):
        error("Dependency installation failed.")
        return 1

    info(f"OmniCore installed successfully in: {Path.cwd()}")
    return 0


if __name__ == "__main__":
    try:
        sys.exit(main())
    except KeyboardInterrupt:
        error("Installation cancelled by user.")
        sys.exit(130)
