<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            background: #0c4a6e;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            z-index: 1;
        }
        .popup button {
            margin-top: 10px;
            padding: 8px 16px;
            cursor: pointer;
        }
    </style>

    <script async src="https://unpkg.com/es-module-shims@1.6.3/dist/es-module-shims.js"></script>

    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.150.1/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.150.1/examples/jsm/"
            }
        }
    </script>
</head>
<body>
    <script type="module">
        import * as THREE from 'three'
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

    let score = 0;
    let level = 1;
    let isPaused = false;
    let isGameOver = false;
    let level2Frames = 1000;


        const scoreElement = document.createElement('div');
        scoreElement.style.position = 'absolute';
        scoreElement.style.top = '10px';
        scoreElement.style.left = '10px';
        scoreElement.style.color = '#ffffff';
        document.body.appendChild(scoreElement);

        const levelElement = document.createElement('div');
        levelElement.style.position = 'absolute';
        levelElement.style.top = '30px';
        levelElement.style.left = '10px';
        levelElement.style.color = '#ffffff';
        document.body.appendChild(levelElement);

        const popup = document.createElement('div');
        popup.classList.add('popup');
        document.body.appendChild(popup);

        const popupText = document.createElement('p');
        popup.appendChild(popupText);

        const popupButton = document.createElement('button');
        popupButton.addEventListener('click', () => {
            hidePopup();
            if (isGameOver) {
                window.location.reload();
            } else {
                level++;
                updateLevel();
                animate();
            }
        });
        popup.appendChild(popupButton);

        function showPopup(message, buttonText) {
            popupText.textContent = message;
            popupButton.textContent = buttonText;
            popup.style.display = 'block';
            isPaused = true;

            // Add the following condition to reload the page when the game is over
            if (isGameOver && buttonText === 'Retry') {
                popupButton.addEventListener('click', () => {
                    window.location.reload();
                });
            } else if (level === 2 && buttonText === 'Continue') {
                popupButton.addEventListener('click', () => {
                    // Reset the game and start from level 1
                    resetGame();
                });
            }
        }

        function hidePopup() {
            popup.style.display = 'none';
            isPaused = false;
        }

    function increaseScore() {
        if (!isPaused) {
            score++;
            updateScore();
            checkLevel();
        }
    }

    function updateScore() {
        scoreElement.textContent = `Score: ${score}`;
    }

    function updateLevel() {
        levelElement.textContent = `Level: ${level}`;
    }

    function checkLevel() {
        // Define the score threshold to move to the next level
        const level1Threshold = 2000;
        const level2Threshold = 4000;

        if (score >= level1Threshold && level === 1) {
            showPopup('Level 1 Completed! Click Continue to go to Level 2.', 'Continue');
        } else if (score >= level2Threshold && level === 2) {
            showPopup('Well Done! You completed Level 2. Click Continue to play again.', 'Continue');
        }
    }

    function resetGame() {
        hidePopup();
        resetScore();
        level = 1;
        updateLevel();
        isGameOver = false;
        level2Frames = 1000;

        // Reset cube position
        cube.position.set(0, 0, 0);
        cube.velocity.set(0, -0.01, 0);

        // Remove existing enemies
        enemies.forEach((enemy) => {
            scene.remove(enemy);
        });
        enemies.length = 0;

        animate();
    }

    function resetScore() {
        score = 0;
        updateScore();
    }

    const scene = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(
        75,
        window.innerWidth / window.innerHeight,
        0.1,
        1000
    )
    camera.position.set(4.61, 2.74, 8)

    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true
    })
    renderer.shadowMap.enabled = true
    renderer.setSize(window.innerWidth, window.innerHeight)
    document.body.appendChild(renderer.domElement)

    const controls = new OrbitControls(camera, renderer.domElement)

   
    class Box extends THREE.Mesh {
      constructor({
        width,
        height,
        depth,
        color = '#00ff00',
        velocity = {
          x: 0,
          y: 0,
          z: 0
        },
        position = {
          x: 0,
          y: 0,
          z: 0
        },
        zAcceleration = false
      }) {
        super(
          new THREE.BoxGeometry(width, height, depth),
          new THREE.MeshStandardMaterial({ color })
        )
  
        this.width = width
        this.height = height
        this.depth = depth
  
        this.position.set(position.x, position.y, position.z)
  
        this.right = this.position.x + this.width / 2
        this.left = this.position.x - this.width / 2
  
        this.bottom = this.position.y - this.height / 2
        this.top = this.position.y + this.height / 2
  
        this.front = this.position.z + this.depth / 2
        this.back = this.position.z - this.depth / 2
  
        this.velocity = velocity
        this.gravity = -0.002
  
        this.zAcceleration = zAcceleration
      }
  
      updateSides() {
        this.right = this.position.x + this.width / 2
        this.left = this.position.x - this.width / 2
  
        this.bottom = this.position.y - this.height / 2
        this.top = this.position.y + this.height / 2
  
        this.front = this.position.z + this.depth / 2
        this.back = this.position.z - this.depth / 2
      }
  
      update(ground) {
        this.updateSides()
  
        if (this.zAcceleration) this.velocity.z += 0.0003
  
        this.position.x += this.velocity.x
        this.position.z += this.velocity.z
  
        this.applyGravity(ground)
      }
  
      applyGravity(ground) {
        this.velocity.y += this.gravity
  
        // this is where we hit the ground
        if (
          boxCollision({
            box1: this,
            box2: ground
          })
        ) {
          const friction = 0.5
          this.velocity.y *= friction
          this.velocity.y = -this.velocity.y
        } else this.position.y += this.velocity.y
      }
    }

    function boxCollision({ box1, box2 }) {
      const xCollision = box1.right >= box2.left && box1.left <= box2.right
      const yCollision =
        box1.bottom + box1.velocity.y <= box2.top && box1.top >= box2.bottom
      const zCollision = box1.front >= box2.back && box1.back <= box2.front
  
      return xCollision && yCollision && zCollision
    }
  
    const cube = new Box({
        width: 1,
        height: 1,
        depth: 1,
        velocity: {
            x: 0,
            y: -0.01,
            z: 0
        }
    })
    cube.castShadow = true
    scene.add(cube)

    const ground = new Box({
        width: 10,
        height: 0.5,
        depth: 50,
        color: '#0369a1',
        position: {
            x: 0,
            y: -2,
            z: 0
        }
    })

    ground.receiveShadow = true
    scene.add(ground)

    const light = new THREE.DirectionalLight(0xffffff, 1)
    light.position.y = 3
    light.position.z = 1
    light.castShadow = true
    scene.add(light)

    scene.add(new THREE.AmbientLight(0xffffff, 0.5))

    camera.position.z = 5

    const keys = {
        a: {
            pressed: false
        },
        d: {
            pressed: false
        },
        s: {
            pressed: false
        },
        w: {
            pressed: false
        }
    }

    window.addEventListener('keydown', (event) => {
        if (isGameOver) return;

        switch (event.code) {
            case 'KeyA':
                keys.a.pressed = true
                break
            case 'KeyD':
                keys.d.pressed = true
                break
            case 'KeyS':
                keys.s.pressed = true
                break
            case 'KeyW':
                keys.w.pressed = true
                break
            case 'Space':
                cube.velocity.y = 0.08
                break
        }
    })

    window.addEventListener('keyup', (event) => {
        if (isGameOver) return;

        switch (event.code) {
            case 'KeyA':
                keys.a.pressed = false
                break
            case 'KeyD':
                keys.d.pressed = false
                break
            case 'KeyS':
                keys.s.pressed = false
                break
            case 'KeyW':
                keys.w.pressed = false
                break
        }
    })

    const enemies = []

    let frames = 0
    let spawnRate = 200
    function animate() {
    const animationId = requestAnimationFrame(animate);

    // Check if the game is paused
    if (isPaused) {
        return;
    }

    renderer.render(scene, camera);

    cube.velocity.x = 0;
    cube.velocity.z = 0;

    if (keys.a.pressed) cube.velocity.x = -0.05;
    else if (keys.d.pressed) cube.velocity.x = 0.05;

    if (keys.s.pressed) cube.velocity.z = 0.05;
    else if (keys.w.pressed) cube.velocity.z = -0.05;

    cube.update(ground);

    enemies.forEach((enemy) => {
        enemy.update(ground);
        if (
            boxCollision({
                box1: cube,
                box2: enemy
            })
        ) {
            isGameOver = true;
            showPopup(`Game Over! Your Score: ${score}. Click Retry to start over.`, 'Retry');
            cancelAnimationFrame(animationId);
        }
    });

    if (level === 2) {
        level2Frames--;

        if (level2Frames <= 0) {
            showPopup('Well Done! You completed Level 2. Click Continue to play again.', 'Continue');
        }
    }

    if (frames % spawnRate === 0) {
        if (level === 1) {
            if (spawnRate > 20) spawnRate -= 20;
            spawnEnemiesForLevel1();
        } else if (level === 2) {
            spawnEnemiesForLevel2();
        }
    }

    increaseScore(); // Increase score on each frame

    frames++;
}

    function spawnEnemiesForLevel1() {
        const enemy = new Box({
            width: 1,
            height: 1,
            depth: 1,
            position: {
                x: (Math.random() - 0.5) * 10,
                y: (Math.random() + 0.9) * 10,
                z: -10
            },
            velocity: {
                x: 0,
                y: 0,
                z: 0.005
            },
            color: 'red',
            zAcceleration: true
        })
        enemy.castShadow = true
        scene.add(enemy)
        enemies.push(enemy)
    }

    function spawnEnemiesForLevel2() {
        const enemy = new Box({
            width: 1,
            height: 1,
            depth: 1,
            position: {
                x: (Math.random() - 0.5) * 10,
                y: (Math.random() + 0.5) * 10,
                z: -20
            },
            velocity: {
                x: 0.005,
                y: 0.005,
                z: 0.005
            },
            color: 'red',
            zAcceleration: true
        })
        enemy.castShadow = true
        scene.add(enemy)
        enemies.push(enemy)
    }

    updateLevel();
    animate();
</script>
