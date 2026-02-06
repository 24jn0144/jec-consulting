<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>期末試験対策クイズ01</title>
  <style>
    body { font-family: sans-serif; max-width: 900px; margin: 20px auto; line-height: 1.6; }
    h1 { font-size: 1.6rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 16px; margin-top: 16px; }
    .choice { margin: 8px 0; }
    button { padding: 8px 16px; margin-top: 12px; cursor: pointer; }
    .result { margin-top: 12px; font-weight: bold; }
    .correct { color: green; }
    .wrong { color: red; }
  </style>
</head>
<body>

<h1>期末試験対策クイズ01（全21問ランダム）</h1>

<div class="card">
  <div id="q-number"></div>
  <div id="question" style="font-weight:bold; margin-bottom:12px;"></div>

  <div id="choices"></div>

  <button id="checkBtn">答え合わせ</button>
  <button id="nextBtn">次の問題</button>

  <div id="result" class="result"></div>
  <div id="correctAnswer" class="result"></div>
</div>

<script>
  // ▼▼▼ 問題文を一切変えずにデータ化 ▼▼▼
  const quizData = [
    {
      q: "[あなたの会社はオンプレミス環境をAzureに移行しました。これはどの展開モデルに該当するでしょうか。]",
      choices: ["ハイブリッドクラウド", "パブリッククラウド", "プライベートクラウド", "サーバーレス"],
      a: ["パブリッククラウド"]
    },
    {
      q: "[カスタムアプリケーションを Azure に展開することを計画しています。アプリケーションには、いくつかの前提条件となるミドルウェアやサービスがあります。次のうちどのサービスモデルを推奨すべきでしょうか]",
      choices: ["Software as a Service (SaaS)", "サーバレス", "Platform as a Service (PaaS)", "Infrastructure as a Service(IaaS)"],
      a: ["Infrastructure as a Service(IaaS)"]
    },
    {
      q: "[次の特徴を表す用語として正しいのは次のうちどれでしょうか。\nサーバの台数や性能を容易に拡大・縮小できる]",
      choices: ["弾力性", "高可用性", "アジリティ", "スケーラビリティ"],
      a: ["スケーラビリティ"]
    },
    {
      q: "[次の説明を確認し、「」の内容が誤りの場合は正しくなるよう回答を選択してください。\n「多要素認証」は、同じ認証を使用して、異なるプロバイダーの複数のリソースやアプリケーションにアクセスする機能です]",
      choices: ["ロールベースアクセス制御", "ゼロトラスト", "シングルサインオン", "変更は必要ありません"],
      a: ["シングルサインオン"]
    },
    {
      q: "[次の説明を確認し、「」の内容が誤りの場合は正しくなるよう回答を選択してください。\nRG01という名前のリソースグループにVNET01という名前の仮想ネットワークがあります。仮想ネットワークの作成/更新を拒否するAzure PolicyをRG01に割り当てると、VNET01は「自動的に削除されます」]",
      choices: ["影響を受けず引き続き機能します", "別のサブスクリプションに移動します", "別のリソースグループに移動します", "変更は必要ありません"],
      a: ["影響を受けず引き続き機能します"]
    },
    {
      q: "[次の説明を確認し、「」の内容が誤りの場合は正しくなるよう回答を選択してください。\nAzure ストレージアカウントのアーカイブアクセス層に格納されているデータは、「azcopy.exeを使用していつでもアクセスできます」]",
      choices: ["変更は必要ありません", "データをリハイドレートする必要があります", "データを復元する必要があります"],
      a: ["データをリハイドレートする必要があります"]
    },
    {
      q: "[あなたはAzureでInfrastructureas a Service（IaaS）リソースをデプロイすることを計画しています。\nIaaSの例として適切なソリューションはどれでしょうか。]",
      choices: ["Cosmos DB", "App Service", "Azure Load Balancer", "Azure SQL Database"],
      a: ["Azure Load Balancer"]
    },
    {
      q: "[あなたは、MIcrosoftEntraIDのユーザーがインターネットから匿名のIPアドレスを使用して接続したときに、ユーザーに対して自動的にパスワードの変更を求めるようにする必要があります。\nどのAzureサービスを使用する必要がありますか？]",
      choices: ["Azure AD Privileged Identity Management", "Azure Monitor", "EntraID Protection", "Microsoft Defender for Cloud"],
      a: ["EntraID Protection"]
    },
    {
      q: "[Azure サブスクリプションの現在の請求期間のコストが指定された金額を超えた場合に電子メールでアラートを送信するためのサービスとして適切なのは次のうちどれでしょうか。]",
      choices: ["Azure Advisor", "コンプライアンスマネージャー", "IAM", "予算アラート"],
      a: ["予算アラート"]
    },
    {
      q: "[あなたの会社には、次の未使用のリソースを含むAzureサブスクリプションがあります。あなたは会社のAzureコストを削減する必要があります。\nどの未使用のリソースを削除する必要がありますか？]",
      choices: ["AzureADの5つのグループ", "AzureActiveDirectoryのユーザーアカウント", "10個のパブリックIPアドレス", "10個のネットワークインターフェイス"],
      a: ["10個のパブリックIPアドレス"]
    },
    {
      q: "[次の文章を完成させるために、空欄に入れるのにふさわしいのは選択肢のうちのどれでしょうか。\n_________から、過去14日間にAzureポータルから特定の仮想マシンを停止したユーザーを表示できます]",
      choices: ["Azure Adviser", "Azure Service Health", "アクティビティログ", "Microsoft Defender for Cloud"],
      a: ["アクティビティログ"]
    },
    {
      q: "[あなたの会社には、複数のサーバーを含むオンプレミス環境があります。\n複数のサーバーをAzure仮想マシンに移行した場合に軽減されるのはどの管理責任でしょうか。2つ選択してください。]",
      choices: ["故障したサーバーハードウェアの交換", "共有ドキュメントへのアクセス許可の管理", "物理サーバーのセキュリティの管理", "サーバのOSの更新"],
      a: ["故障したサーバーハードウェアの交換", "物理サーバーのセキュリティの管理"]
    },
    {
      q: "[次の説明に合致するAzureサービスは選択肢のうちどれでしょうか。\nAzureおよびMicrosoft365でホストされているリソースの認証サービスを提供します]",
      choices: ["Azure Policy", "Azure Active Directory", "Azure Multi-Factor Authentication", "ロールベースのアクセス制御（RBAC）"],
      a: ["Azure Active Directory"]
    },
    {
      q: "[次の説明を確認し、「」の内容が誤りの場合は正しくなるよう回答を選択してください。\nAzure Policyのイニシアチブは「特定のスコープに割り当てられたポリシー」です]",
      choices: ["適用除外のルール", "コンプライアンスのレポート", "変更は必要ありません", "ポリシー定義をグループ化したもの"],
      a: ["ポリシー定義をグループ化したもの"]
    },
    {
      q: "[次の説明を確認し、「」の内容が正しい場合は「変更は必要ありません」を、そうでない場合は説明が正しくなるよう回答を選択してください。\n「リソースグループ」は、Azureリソースのコンプライアンスを管理・評価する機能を組織に提供します]",
      choices: ["Azure Policy", "変更は必要ありません", "管理グループ", "サブスクリプション"],
      a: ["Azure Policy"]
    },
    {
      q: "[ゼロトラストの3つの基本原則として正しくないものはどれでしょうか。]",
      choices: ["明示的に検証する", "ネットワーク境界にファイヤウォールを設置する", "侵害を想定する", "最小特権アクセスを使用する"],
      a: ["ネットワーク境界にファイヤウォールを設置する"]
    },
    {
      q: "[会社のAzure環境が規制コンプライアンス要件を満たしているかどうかを評価するために利用するサービスは次のうちどれでしょうか。]",
      choices: ["トラストセンター", "Azure Adviser", "Microsoft Defender for cloud", "Azure BluePrints"],
      a: ["Microsoft Defender for cloud"]
    },
    {
      q: "[あなたはAndroidスマートフォンからAzure仮想マシンを管理する必要があります。使用できるAzure管理ツールは次のうちどれでしょうか。２つ選びなさい]",
      choices: ["Azure Portal", "Azure CLI", "Azure Cloud Shell", "Azure Storage Explorer"],
      a: ["Azure Portal", "Azure Cloud Shell"]
    },
    {
      q: "[Azure のID管理プロセスについて\n認証　と合致する説明はどれか]",
      choices: ["ユーザーの資格情報を確認するプロセス", "実行する権限を付与するプロセス", "特定の API にアクセスするアクセス許可を持っているかどうかを判別するプロセス", "Azure以外の信頼できるドメインへの接続"],
      a: ["ユーザーの資格情報を確認するプロセス"]
    },
    {
      q: "[次の説明を確認し、「」の内容が誤りの場合は正しくなるよう回答を選択してください。\n「Azure Arc」は、Azureの外部でホストされている Windows および Linux の物理サーバーと仮想マシンを管理することができます]",
      choices: ["Azure Blueprints", "Azure Resource Manager", "Azure Cloud Shell", "変更は必要ありません"],
      a: ["変更は必要ありません"]
    },
    {
      q: "[新たに機能を開発することなくアプリケーションを利用したい場合の選択肢として適切なものは次のうちどれでしょうか。]",
      choices: ["Platform as a Service (PaaS)", "Infrastructure as a Service (IaaS)", "Software as a Service (SaaS)", "サーバレス"],
      a: ["Software as a Service (SaaS)"]
    }
  ];

  // ▼▼▼ ランダム順に並べ替え ▼▼▼
  const order = [...Array(quizData.length).keys()];
  for (let i = order.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [order[i], order[j]] = [order[j], order[i]];
  }

  let current = 0;

  function loadQuestion() {
    if (current >= order.length) {
      document.querySelector(".card").innerHTML =
        "<h2>全21問終了！お疲れさま！</h2><p>もう一度やる場合はページをリロードしてください。</p>";
      return;
    }

    const item = quizData[order[current]];

    document.getElementById("q-number").textContent =
      `問題 ${current + 1} / 全 ${quizData.length} 問`;
    document.getElementById("question").textContent = item.q;

    const choiceArea = document.getElementById("choices");
    choiceArea.innerHTML = "";

    const isMulti = item.a.length > 1;

    item.choices.forEach(c => {
      const div = document.createElement("div");
      div.className = "choice";
      div.innerHTML = `
        <label>
          <input type="${isMulti ? "checkbox" : "radio"}" name="choice" value="${c}">
          ${c}
        </label>`;
      choiceArea.appendChild(div);
    });

    document.getElementById("result").textContent = "";
    document.getElementById("correctAnswer").textContent = "";
  }

  function checkAnswer() {
    const item = quizData[order[current]];
    const correct = item.a;

    const selected = [...document.querySelectorAll("input[name='choice']:checked")]
      .map(i => i.value);

    if (selected.length === 0) {
      document.getElementById("result").textContent = "選択肢を選んでください。";
      return;
    }

    const isCorrect =
      selected.length === correct.length &&
      selected.every(v => correct.includes(v));

    document.getElementById("result").textContent =
      isCorrect ? "◎ 正解！" : "× 不正解";

    document.getElementById("correctAnswer").textContent =
      "正解： " + correct.join("、");
  }

  document.getElementById("checkBtn").addEventListener("click", checkAnswer);
  document.getElementById("nextBtn").addEventListener("click", () => {
    current++;
    loadQuestion();
  });

  loadQuestion();
</script>

</body>
</html>